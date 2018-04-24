<?php

namespace AppBundle\Service;

use AppBundle\Entity\Report\BankAccount;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Entity\User;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\File\FileUploader;
use AppBundle\Service\File\Storage\S3Storage;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSender;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Container;

class ReportSubmissionService
{

    /**
     * @var FileUploader
     */
    private $fileUploader;

    /**
     * @var RestClient
     */
    private $restClient;

    /**
     * @var Templating container
     */
    private $templating;

    /**
     * wkhtmltopdf
     */
    private $wkhtmltopdf;

    /**
     * @var MailSender
     */
    private $mailSender;

    /**
     * @var MailFactory
     */
    private $mailFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ReportSubmissionService constructor.
     * @param Container $container
     * @throws \Exception
     */
    public function __construct(Container $container)
    {
        $this->fileUploader = $container->get('file_uploader');
        $this->restClient = $container->get('rest_client');
        $this->mailSender = $container->get('mail_sender');
        $this->mailFactory =$container->get('mail_factory');
        $this->templating = $container->get('templating');
        $this->wkhtmltopdf = $container->get('wkhtmltopdf');
        $this->translator = $container->get('translator');
        $this->logger =$container->get('logger');
    }

    /**
     * Wrapper method for all documents generated for a report submission
     * @param Report $report
     */
    public function generateReportDocuments(Report $report)
    {
        $this->generateReportPdf($report);
        $this->generateTransactionsCsv($report);
    }

    /**
     * Generates the PDF of the report
     *
     * @param Report $report
     */
    private function generateReportPdf(Report $report)
    {
        // store PDF (with summary info) as a document
        $this->fileUploader->uploadFile(
            $report,
            $this->getPdfBinaryContent($report, true),
            $report->createAttachmentName('DigiRep-%s_%s_%s.pdf'),
            true
        );
    }

    /**
     * Generate the HTML of the report and convert to PDF
     *
     * @param  Report $report
     * @param  bool                    $showSummary
     * @return string                  binary PDF content
     */
    public function getPdfBinaryContent(Report $report, $showSummary = false)
    {
        $html = $this->templating->render('AppBundle:Report/Formatted:formatted_body.html.twig', [
            'report' => $report,
            'showSummary' => $showSummary
        ]);

        return $this->wkhtmltopdf->getPdfFromHtml($html);
    }

    /**
     * @param Report $report
     * @param User $user
     */
    public function submit(Report $report, User $user)
    {
        // store report and get new YEAR report (only for reports submitted the first time)
        $newYearReportId = $this->restClient->put('report/' . $report->getId() . '/submit', $report, ['submit']);
        if ($newYearReportId) {
            $newReport = $this->restClient->get('report/' . $newYearReportId, 'Report\\Report');

            //send confirmation email
            if ($user->isDeputyOrg()) {
                $reportConfirmEmail = $this->mailFactory->createOrgReportSubmissionConfirmationEmail($user, $report, $newReport);
                $this->mailSender->send($reportConfirmEmail, ['text', 'html'], 'secure-smtp');
            } else {
                $reportConfirmEmail = $this->mailFactory->createReportSubmissionConfirmationEmail($user, $report, $newReport);
                $this->mailSender->send($reportConfirmEmail, ['text', 'html']);
            }
        }
    }

    /**
     * Store the report transactions as document entry
     *
     * @param Report $report
     */
    private function generateTransactionsCsv(Report $report)
    {
        // initialize temporary fp
        $this->fd = fopen('php://temp/maxmemory:1048576', 'w');
        if($this->fd === FALSE) {
            die('Failed to open temporary file');
        }

        $this->generateTransactionsCsvContent($report);

        rewind($this->fd);
        $csvContent = stream_get_contents($this->fd);
        fclose($this->fd);

        $this->fileUploader->uploadFile(
            $report,
            $csvContent,
            $report->createAttachmentName('DigiRepTransactions-%s_%s_%s.csv'),
            true
        );
    }


    private function generateTransactionsCsvContent(Report $report)
    {
        //foreach($report->getBankAccounts() as $bankAccount) {
        //$this->generateBankAccountSummary($bankAccount);
        $this->generateCsvHeaders();
        $this->generateTransactionRows($report->getGifts(), 'gift');
        $this->generateTransactionRows($report->getExpenses(), 'expenses');
        $this->generateTransactionRows($report->getMoneyTransactionsOut(), 'money out');
        $this->generateTransactionRows($report->getMoneyTransactionsIn(), 'money in');

        //}
    }

    private function generateBankAccountSummary(BankAccount $bankAccount)
    {
        $summaryFields = [
            [" "],
            [" "],
            ["ACCOUNT SUMMARY"],
            [$bankAccount->getBank() . "   -   " . ucfirst($bankAccount->getAccountType()) ." account" ],
            ["**** " . $bankAccount->getAccountNumber() . "      (" . $bankAccount->getSortCode(). ")"],
            [$bankAccount->getIsJointAccount() ? "JOINT ACCOUNT" : ""],
            [" "],
            [" "],


        ];

        foreach($summaryFields as $line)
        {
            fputcsv($this->fd, $line);
        }
    }

    private function generateCsvHeaders()
    {
        $headers = ['Type', 'Category' ,'Amount', 'Account', 'Description'];
        fputcsv($this->fd, $headers);
    }

    private function generateTransactionRows($transactions, $type)
    {
        foreach($transactions as $t) {
            /** @var $t \AppBundle\Entity\Report\MoneyTransaction */
            fputcsv(
                $this->fd, [
                    ucFirst($type),
                    (property_exists($t, 'category') ?
                        $this->translator->trans(
                            'form.category.entries.' . $t->getCategory().'.label', [], 'report-money-transaction') : ''),
                    $t->getAmount(),
                    (!empty($t->getBankAccount()) ? $t->getBankAccount()->getDisplayName() : "UNASSIGNED"),
                    (property_exists($t, 'description') ? $t->getDescription() : '')
                ]
            );
        }

    }

}
