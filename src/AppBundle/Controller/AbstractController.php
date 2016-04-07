<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Client;
use AppBundle\Entity\Report;
use AppBundle\Service\Client\RestClient;

class AbstractController extends Controller
{
    /**
     * @return RestClient
     */
    protected function getRestClient()
    {
        return $this->get('restClient');
    }
    
    /**
     * @return array $choices
     */
    public function getAllowedCourtOrderTypeChoiceOptions(array $filter = [], $sort = null)
    {
        $responseArray = $this->getRestClient()->get('court-order-type', 'array');

        if (!empty($filter)) {
            foreach ($responseArray['court_order_types'] as $value) {
                if (in_array($value['id'], $filter)) {
                    $choices[$value['id']] = $value['name'];
                }
            }
        } else {
            foreach ($responseArray['court_order_types'] as $value) {
                $choices[$value['id']] = $value['name'];
            }
        }

        if ($sort != null) {
            $sort($choices);
        }
        return $choices;
    }




    /**
     * @param integer $reportId
     * @param integer $userId for secutity checks (if present)
     * @param array $groups
     * 
     * @return Report
     */
    public function getReport($reportId, array $groups/* = [ 'transactions', 'basic']*/)
    {
        return $this->getRestClient()->get("report/{$reportId}", 'Report', [ 'query' => [ 'groups' => $groups]]);
    }


    /**
     * @param Client $client
     * 
     * @return Report[]
     */
    public function getReportsIndexedById(Client $client, $groups)
    {
        $reportIds = $client->getReports();

        if (empty($reportIds)) {
            return [];
        }

        $ret = [];
        foreach ($reportIds as $id) {
            $ret[$id] = $this->getReport($id,$groups);
        }

        return $ret;
    }

    /**
     *
     * @param integer $reportId
     * @return \AppBundle\Entity\Report
     *
     * @throws \RuntimeException if report is submitted
     */
    protected function getReportIfReportNotSubmitted($reportId, array $groups)
    {
        $report = $this->getReport($reportId, $groups);
        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }

        return $report;
    }
    
    /**
     * Display a success message on the header
     */
    protected function flashSuccess()
    {
        $this->addFlash('flash-top', 'success');
    }
    
    /**
     * Display a failure message on the header
     */
    protected function flashFailure()
    {
        $this->addFlash('flash-top', 'failure');
    }

}
