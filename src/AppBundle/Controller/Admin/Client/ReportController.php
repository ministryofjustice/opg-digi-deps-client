<?php

namespace AppBundle\Controller\Admin\Client;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Entity\Report\Checklist;
use AppBundle\Entity\Report\Report;
use AppBundle\Exception\DisplayableException;
use AppBundle\Form\Admin\ReportChecklistType;
use AppBundle\Form\Admin\UnsubmitReportType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin/report/{id}/", requirements={"id":"\d+"})
 */
class ReportController extends AbstractController
{
    /**
     * JMS groups used for report preview and PDF
     * //TODO consider take/merge the value from ReportController::$reportGroupsAll
     * @var array
     */
    private static $reportGroupsAll = [
        'report',
        'client',
        'account',
        'expenses',
        'fee',
        'gifts',
        'prof-deputy-other-costs',
        'prof-deputy-costs-how-charged',
        'report-prof-deputy-costs',
        'report-prof-deputy-costs-prev', 'prof-deputy-costs-prev',
        'report-prof-deputy-costs-interim', 'prof-deputy-costs-interim',
        'report-prof-deputy-costs-scco',
        'report-prof-deputy-fixed-cost',
        'action',
        'action-more-info',
        'asset',
        'debt',
        'debt-management',
        'fee',
        'balance',
        'contact',
        'debts',
        'decision',
        'visits-care',
        'lifestyle',
        'mental-capacity',
        'money-transfer',
        'transaction',
        'transactionsIn',
        'transactionsOut',
        'moneyShortCategoriesIn',
        'moneyShortCategoriesOut',
        'moneyTransactionsShortIn',
        'moneyTransactionsShortOut',
        'status',
        'report-submitted-by',
        'wish-to-provide-documentation',
        'report-documents',
        'balance-state',
        'documents',
        'report-prof-service-fees',
        'prof-service-fees',
        'client-named-deputy'
    ];

    /**
     * @Route("manage", name="admin_report_manage")
     * //TODO define Security group (AD to remove?)
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD') or has_role('ROLE_CASE_MANAGER')")
     * @param Request $request
     * @param $id
     *
     * @Template()
     *
     * @return array
     */
    public function manageAction(Request $request, $id)
    {
        $report = $this->getReport($id, []);
        $reportDueDate = $report->getDueDate();

        if (!$report->getSubmitted()) {
            throw new DisplayableException('Only submitted report can be managed');
        }

        $form = $this->createForm(UnsubmitReportType::class, $report);
        $form->handleRequest($request);

        // edit client form
        if ($form->isValid()) {
            $report
                ->setUnSubmitDate(new \DateTime())
                ->setUnsubmittedSectionsList(implode(',', $report->getUnsubmittedSectionsIds()))
            ;

            $dueDateChoice = $form['dueDateChoice']->getData();
            if ($dueDateChoice == UnsubmitReportType::DUE_DATE_OPTION_CUSTOM) {
                $report->setDueDate($form['dueDateCustom']->getData());
            } elseif (preg_match('/^\d+$/', $dueDateChoice)) {
                $dd = new \DateTime();
                $dd->modify("+{$dueDateChoice} weeks");
                $report->setDueDate($dd);
            }

            $this->getRestClient()->put('report/' . $report->getId() . '/unsubmit', $report, [
                'submitted', 'unsubmit_date', 'report_unsubmitted_sections_list', 'report_due_date', 'startEndDates'
            ]);
            $request->getSession()->getFlashBag()->add('notice', 'Report marked as incomplete');

            return $this->redirect($this->generateUrl('admin_client_details', ['id'=>$report->getClient()->getId()]));
        }

        return [
            'report'   => $report,
            'reportDueDate'   => $reportDueDate,
            'form'     => $form->createView()
        ];
    }

    /**
     * @Route("checklist", name="admin_report_checklist")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_CASE_MANAGER')")
     * @param Request $request
     * @param $id
     *
     * @Template()
     *
     * @return array
     */
    public function checklistAction(Request $request, $id)
    {
        $report = $this->getReport(
            $id,
            array_merge(
                self::$reportGroupsAll,
                [
                    'report-checklist', 'checklist-information', 'last-modified', 'user', 'previous-report-data'
                ]
            )
        );

        if (!$report->getSubmitted() && empty($report->getUnSubmitDate())) {
            throw new DisplayableException('Cannot lodge a checklist for an incomplete report');
        }

        $checklist = $report->getChecklist();
        $checklist = empty($checklist) ? new Checklist($report) : $checklist;
        $form = $this->createForm(ReportChecklistType::class, $checklist, ['report' => $report]);
        $form->handleRequest($request);
        $buttonClicked = $form->getClickedButton();

        if ($buttonClicked instanceof SubmitButton) {
            $checklist->setButtonClicked($buttonClicked->getName());
        }
        if ($form->isValid($buttonClicked)) {

            if (!empty($checklist->getId())) {
                $this->getRestClient()->put('report/' . $report->getId() . '/checked', $checklist, [
                    'report-checklist', 'checklist-information'
                ]);
            } else {
                $this->getRestClient()->post('report/' . $report->getId() . '/checked', $checklist, [
                    'report-checklist', 'checklist-information'
                ]);
            }
            if (!$request->getSession()->getFlashBag()->has('notice')) {
                // the duplicate notice is because the PDF view action doesn't actually refresh the page and therefore the original
                // 'saved' notice never gets rendered
                $request->getSession()->getFlashBag()->add('notice', 'Lodging checklist saved');
            }

            if ($buttonClicked->getName() == 'saveFurtherInformation') {
                return $this->redirect(
                    $this->generateUrl('admin_report_checklist', ['id'=>$report->getId()]) . '#furtherInformation'
                );
            } else {
                if ($buttonClicked->getName() == 'submitAndDownload') {
                    return $this->checklistPDFViewAction($report->getId());
                } else {
                    return $this->redirect($this->generateUrl('admin_report_checklist', ['id'=>$report->getId()]) . '#');
                }
            }
        }

        return [
            'report'   => $report,
            'form'     => $form->createView(),
            'checklist' => $checklist,
            'previousReportData' => $report->getPreviousReportData()
        ];
    }

    /**
     * Generate and return Checklist as Response object
     *
     * @Route("checklist-{reportId}.pdf", name="admin_checklist_pdf")
     *
     * @param $reportId
     * @return Response
     */
    public function checklistPDFViewAction($reportId)
    {
        $report = $this->getReport($reportId, array_merge(self::$reportGroupsAll, ['client', 'report', 'report-checklist', 'checklist-information', 'user']));
        $pdfBinary = $this->get('report_submission_service')->getChecklistPdfBinaryContent($report);

        $response = new Response($pdfBinary);
        $response->headers->set('Content-Type', 'application/pdf');

        $attachmentName = sprintf('DigiChecklist-%s_%s_%s.pdf',
            $report->getEndDate()->format('Y'),
            $report->getSubmitDate() ? $report->getSubmitDate()->format('Y-m-d') : 'n-a-', //some old reports have no submission date
            $report->getClient()->getCaseNumber()
        );

        $response->headers->set('Content-Disposition', 'attachment; filename="' . $attachmentName . '"');

        // Send headers before outputting anything
        $response->sendHeaders();

        return $response;
    }
}
