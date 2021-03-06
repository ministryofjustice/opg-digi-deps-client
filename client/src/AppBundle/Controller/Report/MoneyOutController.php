<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;

use AppBundle\Service\StepRedirector;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class MoneyOutController extends AbstractController
{
    private static $jmsGroups = [
        'transactionsOut',
        'money-out-state',
        'account'
    ];

    /**
     * @Route("/report/{reportId}/money-out", name="money_out")
     * @Template("AppBundle:Report/MoneyOut:start.html.twig")
     */
    public function startAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if ($report->getStatus()->getMoneyOutState()['state'] != EntityDir\Report\Status::STATE_NOT_STARTED) {
            return $this->redirectToRoute('money_out_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/money-out/step{step}/{transactionId}", name="money_out_step", requirements={"step":"\d+"})
     * @Template("AppBundle:Report/MoneyOut:step.html.twig")
     */
    public function stepAction(Request $request, $reportId, $step, $transactionId = null)
    {
        $totalSteps = 2;
        if ($step < 1 || $step > $totalSteps) {
            return $this->redirectToRoute('money_out_summary', ['reportId' => $reportId]);
        }

        // common vars and data
        $dataFromUrl = $request->get('data') ?: [];
        $stepUrlData = $dataFromUrl;
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $fromPage = $request->get('from');

        $stepRedirector = $this->stepRedirector()
            ->setRoutes('money_out', 'money_out_step', 'money_out_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)->setTotalSteps($totalSteps)
            ->setRouteBaseParams(['reportId'=>$reportId, 'transactionId' => $transactionId]);

        // create (add mode) or load transaction (edit mode)
        if ($transactionId) {
            $transaction = array_filter($report->getMoneyTransactionsOut(), function ($t) use ($transactionId) {
                if ($t->getBankAccount() instanceof EntityDir\Report\BankAccount) {
                    $t->setBankAccountId($t->getBankAccount()->getId());
                }
                return $t->getId() == $transactionId;
            });
            $transaction = array_shift($transaction);
        } else {
            $transaction = new EntityDir\Report\MoneyTransaction();
        }

        // add URL-data into model
        isset($dataFromUrl['category']) && $transaction->setCategory($dataFromUrl['category']);
        $stepRedirector->setStepUrlAdditionalParams([
            'data' => $dataFromUrl
        ]);

        // crete and handle form
        $form = $this->createForm(FormDir\Report\MoneyTransactionType::class, $transaction, [
            'step' => $step,
            'type'             => 'out',
            'selectedCategory' => $transaction->getCategory(),
//            'userRole' => $this->getUser()->getRoleName(),
            'authChecker' => $this->get('security.authorization_checker'),
            'report' => $report
            ]
        );
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {
            // decide what data in the partial form needs to be passed to next step
            if ($step == 1) {
                // unset from page to prevent step redirector skipping step 2
                $stepRedirector->setFromPage(null);

                $stepUrlData['category'] = $transaction->getCategory();
            } elseif ($step == $totalSteps) {
                if ($transactionId) { // edit
                    $request->getSession()->getFlashBag()->add(
                        'notice',
                        'Entry edited'
                    );
                    $this->getRestClient()->put('/report/' . $reportId . '/money-transaction/' . $transactionId, $transaction, ['transaction', 'account']);
                    return $this->redirectToRoute('money_out_summary', ['reportId' => $reportId]);
                } else { // add
                    $this->getRestClient()->post('/report/' . $reportId . '/money-transaction', $transaction, ['transaction', 'account']);
                    return $this->redirectToRoute('money_out_add_another', ['reportId' => $reportId]);
                }
            }

            $stepRedirector->setStepUrlAdditionalParams([
                'data' => $stepUrlData
            ]);

            return $this->redirect($stepRedirector->getRedirectLinkAfterSaving());
        }

        return [
            'transaction' => $transaction,
            'report' => $report,
            'step' => $step,
            'reportStatus' => $report->getStatus(),
            'form' => $form->createView(),
            'backLink' => $stepRedirector->getBackLink(),
            'skipLink' => null,
            'categoriesGrouped' => EntityDir\Report\MoneyTransaction::getCategoriesGrouped('out')
        ];
    }

    /**
     * @Route("/report/{reportId}/money-out/add_another", name="money_out_add_another")
     * @Template("AppBundle:Report/MoneyOut:addAnother.html.twig")
     */
    public function addAnotherAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId);

        $form = $this->createForm(FormDir\AddAnotherRecordType::class, $report, ['translation_domain' => 'report-money-transaction']);
        $form->handleRequest($request);

        if ($form->isValid()) {
            switch ($form['addAnother']->getData()) {
                case 'yes':
                    return $this->redirectToRoute('money_out_step', ['reportId' => $reportId, 'step' => 1]);
                case 'no':
                    return $this->redirectToRoute('money_out_summary', ['reportId' => $reportId]);
            }
        }

        return [
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/money-out/summary", name="money_out_summary")
     *
     * @param int $reportId
     * @Template("AppBundle:Report/MoneyOut:summary.html.twig")
     *
     * @return array
     */
    public function summaryAction($reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if ($report->getStatus()->getMoneyOutState()['state'] == EntityDir\Report\Status::STATE_NOT_STARTED) {
            return $this->redirectToRoute('money_out', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/money-out/{transactionId}/delete", name="money_out_delete")
     * @Template("AppBundle:Common:confirmDelete.html.twig")
     *
     * @param int $reportId
     * @param int $transactionId
     *
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, $reportId, $transactionId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        foreach ($report->getMoneyTransactionsOut() as $t) {
            if ($t->getId() === $transactionId) {
                $transaction = $t;
                break;
            }
        }

        if (!isset($transaction)) {
            throw new \RuntimeException('Transaction not found');
        }

        $form = $this->createForm(FormDir\ConfirmDeleteType::class);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->getRestClient()->delete('/report/' . $reportId . '/money-transaction/' . $transactionId);

            $request->getSession()->getFlashBag()->add(
                'notice',
                'Entry deleted'
            );

            return $this->redirect($this->generateUrl('money_out_summary', ['reportId' => $reportId]));
        }

        $translator = $this->get('translator');
        $categoryKey = 'form.category.entries.' . $transaction->getCategory() . '.label';
        $summary = [
            ['label' => 'deletePage.summary.category', 'value' => $translator->trans($categoryKey, [], 'report-money-transaction')],
            ['label' => 'deletePage.summary.description', 'value' => $transaction->getDescription()],
            ['label' => 'deletePage.summary.amount', 'value' => $transaction->getAmount(), 'format' => 'money'],
        ];

        if ($report->canLinkToBankAccounts() && $transaction->getBankAccount()) {
            $summary[] = ['label' => 'deletePage.summary.bankAccount', 'value' => $transaction->getBankAccount()->getNameOneLine()];
        }

        return [
            'translationDomain' => 'report-money-out',
            'report' => $report,
            'form' => $form->createView(),
            'summary' => $summary,
            'backLink' => $this->generateUrl('money_out_summary', ['reportId' => $reportId]),
        ];
    }

    /**
     * @return string
     */
    protected function getSectionId()
    {
        return 'moneyOut';
    }
}
