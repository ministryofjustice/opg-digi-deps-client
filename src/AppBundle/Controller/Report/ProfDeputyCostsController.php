<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * Base route
 *
 * @Route("/report/{reportId}/prof-deputy-costs")
 */
class ProfDeputyCostsController extends AbstractController
{
    private static $jmsGroups = [
        'status',
        // TODO
    ];

    /**
     * @Route("", name="prof_deputy_costs")
     * @Template()
     *
     * @param int $reportId
     *
     * @return array
     */
    public function startAction($reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if ($report->getStatus()->getProfDeputyCostsState()['state'] != EntityDir\Report\Status::STATE_NOT_STARTED) {
            return $this->redirectToRoute('prof_deputy_costs_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }


    /**
     * @return string
     */
    protected function getSectionId()
    {
        return 'profDeputyCosts';
    }
}
