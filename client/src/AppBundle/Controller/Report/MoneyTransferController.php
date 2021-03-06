<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class MoneyTransferController extends AbstractController
{
    private static $jmsGroups = [
        'money-transfer',
        'account',
        'money-transfer-state',
    ];

    /**
     * @Route("/report/{reportId}/money-transfers", name="money_transfers")
     * @Template("AppBundle:Report/MoneyTransfer:start.html.twig")
     *
     * @param int $reportId
     *
     * @return array
     */
    public function startAction($reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (!$report->enoughBankAccountForTransfers()) {
            return $this->render('AppBundle:Report/MoneyTransfer:error.html.twig', [
                'error' => 'atLeastTwoBankAccounts',
                'report' => $report,
            ]);
        }

        if ($report->getStatus()->getMoneyTransferState()['state'] != EntityDir\Report\Status::STATE_NOT_STARTED) {
            return $this->redirectToRoute('money_transfers_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/money-transfers/exist", name="money_transfers_exist")
     * @Template("AppBundle:Report/MoneyTransfer:exist.html.twig")
     */
    public function existAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(FormDir\YesNoType::class, $report, [
            'field'              => 'noTransfersToAdd',
            'translation_domain' => 'report-money-transfer',
            'choices'            => ['Yes' => 0, 'No' => 1]
        ]);

        $form->handleRequest($request);
        if ($form->isValid()) {

            switch ($report->getNoTransfersToAdd()) {
                case 0:
                    return $this->redirectToRoute('money_transfers_step', ['reportId' => $reportId, 'step' => 1]);
                case 1:
                    $this->getRestClient()->put('report/' . $reportId, $report, ['money-transfers-no-transfers']);
                    return $this->redirectToRoute('money_transfers_summary', ['reportId' => $reportId]);
            }
        }

        $backLink = $this->generateUrl('money_transfers', ['reportId' => $reportId]);
        if ($request->get('from') == 'summary') {
            $backLink = $this->generateUrl('money_transfers_summary', ['reportId' => $reportId]);
        }

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/money-transfers/step{step}/{transferId}", name="money_transfers_step", requirements={"step":"\d+"})
     * @Template("AppBundle:Report/MoneyTransfer:step.html.twig")
     */
    public function stepAction(Request $request, $reportId, $step, $transferId = null)
    {
        $totalSteps = 2;
        if ($step < 1 || $step > $totalSteps) {
            return $this->redirectToRoute('money_transfers_summary', ['reportId' => $reportId]);
        }

        // common vars and data
        $dataFromUrl = $request->get('data') ?: [];
        $stepUrlData = $dataFromUrl;
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $fromPage = $request->get('from');

        $stepRedirector = $this->stepRedirector()
            ->setRoutes('money_transfers', 'money_transfers_step', 'money_transfers_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)->setTotalSteps($totalSteps)
            ->setRouteBaseParams(['reportId' => $reportId, 'transferId' => $transferId]);


        // create (add mode) or load transaction (edit mode)
        if ($transferId) {
            $transfer = $report->getMoneyTransferWithId($transferId);
            $transfer->setAccountFromId($transfer->getAccountFrom()->getId());
            $transfer->setAccountToId($transfer->getAccountTo()->getId());
        } else {
            $transfer = new EntityDir\Report\MoneyTransfer();
        }

        // add URL-data into model
        if (isset($dataFromUrl['from-id']) && isset($dataFromUrl['to-id'])) {
            $transfer->setAccountFromId($dataFromUrl['from-id']);
            $transfer->setAccountFrom($report->getBankAccountById($dataFromUrl['from-id']));
            $transfer->setAccountToId($dataFromUrl['to-id']);
            $transfer->setAccountTo($report->getBankAccountById($dataFromUrl['to-id']));
        }
        $stepRedirector->setStepUrlAdditionalParams([
            'data' => $dataFromUrl
        ]);

        // create and handle form
        $form = $this->createForm(FormDir\Report\MoneyTransferType::class, $transfer, ['step' => $step, 'banks' => $report->getBankAccounts()]);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {
            // decide what data in the partial form needs to be passed to next step
            if ($step == 1) {
                $stepUrlData['from-id'] = $transfer->getAccountFromId();
                $stepUrlData['to-id'] = $transfer->getAccountToId();
            } elseif ($step == $totalSteps) {
                if ($transferId) { // edit
                    $request->getSession()->getFlashBag()->add(
                        'notice',
                        'Entry edited'
                    );
                    $this->getRestClient()->put('/report/' . $reportId . '/money-transfers/' . $transferId, $transfer, ['money-transfer']);

                    return $this->redirectToRoute('money_transfers_summary', ['reportId' => $reportId]);
                } else { // add
                    $this->getRestClient()->post('/report/' . $reportId . '/money-transfers', $transfer, ['money-transfer']);
                    return $this->redirectToRoute('money_transfers_add_another', ['reportId' => $reportId]);
                }
            }

            $stepRedirector->setStepUrlAdditionalParams([
                'data' => $stepUrlData
            ]);

            return $this->redirect($stepRedirector->getRedirectLinkAfterSaving());
        }

        return [
            'transfer' => $transfer,
            'report' => $report,
            'step' => $step,
            'reportStatus' => $report->getStatus(),
            'form' => $form->createView(),
            'backLink' => $stepRedirector->getBackLink(),
            'skipLink' => null,
        ];
    }

    /**
     * @Route("/report/{reportId}/money-transfers/add_another", name="money_transfers_add_another")
     * @Template("AppBundle:Report/MoneyTransfer:addAnother.html.twig")
     */
    public function addAnotherAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $form = $this->createForm(FormDir\AddAnotherRecordType::class, $report, ['translation_domain' => 'report-money-transfer']);
        $form->handleRequest($request);

        if ($form->isValid()) {
            switch ($form['addAnother']->getData()) {
                case 'yes':
                    return $this->redirectToRoute('money_transfers_step', ['reportId' => $reportId, 'from' => 'another', 'step' => 1]);
                case 'no':
                    return $this->redirectToRoute('money_transfers_summary', ['reportId' => $reportId]);
            }
        }

        return [
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/money-transfers/summary", name="money_transfers_summary")
     * @Template("AppBundle:Report/MoneyTransfer:summary.html.twig")
     *
     * @param int $reportId
     *
     * @return array
     */
    public function summaryAction($reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if ($report->getStatus()->getMoneyTransferState()['state'] == EntityDir\Report\Status::STATE_NOT_STARTED) {
            return $this->redirect($this->generateUrl('money_transfers', ['reportId' => $reportId]));
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/money-transfers/{transferId}/delete", name="money_transfers_delete")
     * @Template("AppBundle:Common:confirmDelete.html.twig")
     *
     * @param int $id
     *
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, $reportId, $transferId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $form = $this->createForm(FormDir\ConfirmDeleteType::class);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->getRestClient()->delete("/report/{$reportId}/money-transfers/{$transferId}");

            $request->getSession()->getFlashBag()->add(
                'notice',
                'Money transfer deleted'
            );

            return $this->redirect($this->generateUrl('money_transfers_summary', ['reportId' => $reportId]));
        }

        $transfer = $report->getMoneyTransferWithId($transferId);

        return [
            'translationDomain' => 'report-money-transfer',
            'report' => $report,
            'form' => $form->createView(),
            'summary' => [
                ['label' => 'deletePage.summary.accountFrom', 'value' => $transfer->getAccountFrom()->getNameOneLine()],
                ['label' => 'deletePage.summary.accountTo', 'value' => $transfer->getAccountTo()->getNameOneLine()],
                ['label' => 'deletePage.summary.amount', 'value' => $transfer->getAmount(), 'format' => 'money'],
            ],
            'backLink' => $this->generateUrl('money_transfers_summary', ['reportId' => $reportId]),
        ];
    }

    /**
     * @return string
     */
    protected function getSectionId()
    {
        return 'moneyTransfers';
    }
}
