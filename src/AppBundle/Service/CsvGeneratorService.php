<?php

namespace AppBundle\Service;

use AppBundle\Entity\Report\BankAccount;
use AppBundle\Entity\ReportInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class CsvGeneratorService
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CSV file pointer
     */
    private $fd;

    /**
     * CsvGeneratorService constructor.
     * @param TranslatorInterface $translator
     * @param LoggerInterface $logger
     */
    public function __construct(TranslatorInterface $translator, LoggerInterface $logger)
    {
        $this->translator = $translator;
        $this->logger =$logger;
    }

    /**
     * Generate the report transactions as csv
     *
     * @param ReportInterface $report
     *
     * @return string Csv content
     */
    public function generateTransactionsCsv(ReportInterface $report)
    {
        $this->logger->info('Generating Transaction CSV for Report: ' . $report->getId());

        // initialize temporary fp
        $this->fd = fopen('php://temp/maxmemory:1048576', 'w');
        if($this->fd === FALSE) {
            $this->logger->error('Failed to open Temporary file');
            die('Failed to open temporary file');
        }

        $this->generateTransactionsCsvLines($report);

        rewind($this->fd);
        $csvContent = stream_get_contents($this->fd);
        fclose($this->fd);

        return $csvContent;
    }

    /**
     * Generates the lines of the CSV
     *
     * @param ReportInterface $report
     */
    private function generateTransactionsCsvLines(ReportInterface $report)
    {
        //foreach($report->getBankAccounts() as $bankAccount) {
        //$this->generateBankAccountSummary($bankAccount);
        $this->generateCsvHeaders();
        $this->generateTransactionRows($report->getGifts(), 'gift');
        $this->generateTransactionRows($report->getExpenses(), 'expense');
        $this->generateTransactionRows($report->getMoneyTransactionsOut(), 'money out');
        $this->generateTransactionRows($report->getMoneyTransactionsIn(), 'money in');

        //}
    }

    /**
     * Generates a Bank Account Summary to the CSV file pointer
     *
     * @param BankAccount $bankAccount
     */
    private function generateBankAccountSummary(BankAccount $bankAccount)
    {
        // @todo remove following feedback on whether an account summary is required
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

    /**
     * Generate CSV Headers
     */
    private function generateCsvHeaders()
    {
        $headers = ['Type', 'Category' ,'Amount', 'Bank name', 'Account details', 'Description'];
        fputcsv($this->fd, $headers);
    }

    /**
     * Generates Transaction row
     *
     * @param $transactions
     * @param $type
     */
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
                    (!empty($t->getBankAccount()) ? $t->getBankAccount()->getBank() : ""),
                    (!empty($t->getBankAccount()) ? $t->getBankAccount()->getDisplayName() : ""),
                    $this->generateDescription($t)
                ]
            );
        }
    }

    /**
     * Generates a description. Expenses and Gifts have an 'explanation' property,
     * Money transactions have a description property.
     *
     * @param $transaction
     * @return string
     */
    private function generateDescription($transaction)
    {
        if (method_exists($transaction, 'getDescription')) {
            return $transaction->getDescription();
        }

        if (method_exists($transaction, 'getExplanation')) {
            return $transaction->getExplanation();
        }
        return '';
    }
}
