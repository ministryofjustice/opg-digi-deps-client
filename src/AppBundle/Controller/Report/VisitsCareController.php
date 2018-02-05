<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;

use AppBundle\Service\StepRedirector;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class VisitsCareController extends AbstractController
{
    private static $jmsGroups = [
        'visits-care',
        'visits-care-state',
    ];

    /**
     * @Route("/report/{reportId}/visits-care", name="visits_care")
     * @Template()
     */
    public function startAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if ($report->getStatus()->getVisitsCareState()['state'] != EntityDir\Report\Status::STATE_NOT_STARTED) {
            return $this->redirectToRoute('visits_care_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/visits-care/step/{step}", name="visits_care_step")
     * @Template()
     */
    public function stepAction(Request $request, $reportId, $step)
    {
        $totalSteps = 4;
        if ($step < 1 || $step > $totalSteps) {
            return $this->redirectToRoute('visits_care_summary', ['reportId' => $reportId]);
        }
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $visitsCare = $report->getVisitsCare() ?: new EntityDir\Report\VisitsCare();
        $fromPage = $request->get('from');

        $stepRedirector = $this->stepRedirector()
            ->setRoutes('visits_care', 'visits_care_step', 'visits_care_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)->setTotalSteps($totalSteps)
            ->setRouteBaseParams(['reportId' => $reportId]);

        $form = $this->createForm(FormDir\Report\VisitsCareType::class, $visitsCare, [ 'step'            => $step, 'translator'      => $this->get('translator'), 'clientFirstName' => $report->getClient()->getFirstname()
                                   ]
                                 );
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {
            $data = $form->getData();
            /* @var $data EntityDir\Report\VisitsCare */
            $data
                ->setReport($report)
                ->keepOnlyRelevantVisitsCareData();

            if ($visitsCare->getId() == null) {
                $this->getRestClient()->post('report/visits-care', $data, ['visits-care', 'report-id']);
            } else {
                $this->getRestClient()->put('report/visits-care/' . $visitsCare->getId(), $data, self::$jmsGroups);
            }

            if ($fromPage == 'summary') {
                $request->getSession()->getFlashBag()->add(
                    'notice',
                    'Answer edited'
                );
            }

            return $this->redirect($stepRedirector->getRedirectLinkAfterSaving());
        }


        return [
            'report'       => $report,
            'step'         => $step,
            'reportStatus' => $report->getStatus(),
            'form'         => $form->createView(),
            'backLink'     => $stepRedirector->getBackLink(),
            'skipLink'     => $stepRedirector->getSkipLink(),
        ];
    }

    /**
     * @Route("/report/{reportId}/visits-care/summary", name="visits_care_summary")
     * @Template()
     */
    public function summaryAction(Request $request, $reportId)
    {
        $fromPage = $request->get('from');
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if ($report->getStatus()->getVisitsCareState()['state'] == EntityDir\Report\Status::STATE_NOT_STARTED && $fromPage != 'skip-step') {
            return $this->redirectToRoute('visits_care', ['reportId' => $reportId]);
        }

        if (!$report->getVisitsCare()) { //allow validation with answers all skipped
            $report->setVisitsCare(new EntityDir\Report\VisitsCare());
        }

        return [
            'comingFromLastStep' => $fromPage == 'skip-step' || $fromPage == 'last-step',
            'report'             => $report,
            'status'             => $report->getStatus()
        ];
    }

    /**
     * @return string
     */
    protected function getSectionId()
    {
        return 'visitsCare';
    }
}
