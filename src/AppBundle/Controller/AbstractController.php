<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\User;
use AppBundle\Exception\DisplayableException;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\StepRedirector;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

abstract class AbstractController extends Controller
{
    /**
     * @return RestClient
     */
    protected function getRestClient()
    {
        return $this->get('rest_client');
    }

    /**
     * @param array $jmsGroups
     *
     * @return User
     */
    protected function getUserWithData(array $jmsGroups = [])
    {
        $jmsGroups[] = 'user';
        $jmsGroups = array_unique($jmsGroups);
        sort($jmsGroups);

        return $this->getRestClient()->get('user/' . $this->getUser()->getId(), 'User', $jmsGroups);
    }

    /**
     * @return Client|null
     */
    protected function getFirstClient($groups = ['user', 'user-clients', 'client'])
    {
        $user = $this->getUserWithData($groups);

        /* @var $user User */
        $clients = $user->getClients();

        return (is_array($clients) && !empty($clients[0]) && $clients[0] instanceof Client) ? $clients[0] : null;
    }

    /**
     * @param Client $client
     * @param array  $groups
     *
     * @return Report[]
     */
    public function getReportsIndexedById(Client $client, $groups = [])
    {
        $reports = $client->getReports();

        if (empty($reports)) {
            return [];
        }

        $ret = [];
        foreach ($reports as $report) {
            $reportId = $report->getId();
            $ret[$reportId] = $this->getReport($reportId, $groups);
        }

        return $ret;
    }

    /**
     * @param int   $reportId
     * @param array $groups
     *
     * @return Report
     */
    public function getReport($reportId, array $groups = [])
    {
        $groups[] = 'report';
        $groups[] = 'report-client';
        $groups[] = 'client';
        $groups = array_unique($groups);
        sort($groups); // helps HTTP caching
        return $this->getRestClient()->get("report/{$reportId}", 'Report\\Report', $groups);
    }

    /**
     * @param int   $reportId
     * @param array $groups
     *
     * @throws \RuntimeException if report is submitted
     *
     * @return Report
     *
     */
    protected function getReportIfNotSubmitted($reportId, array $groups = [])
    {
        $report = $this->getReport($reportId, $groups);

        $sectionId = $this->getSectionId();
        if ($sectionId && !$report->hasSection($sectionId)) {
            throw new DisplayableException('Section not accessible with this report type.');
        }

        if ($report->getSubmitted()) {
            throw new \RuntimeException('Report already submitted and not editable.');
        }

        return $report;
    }

    /**
     * @param int   $ndrId
     * @param array $groups
     *
     * @return Ndr
     */
    public function getNdr($ndrId, array $groups)
    {
        $groups[] = 'ndr';
        $groups[] = 'ndr-client';
        $groups[] = 'client';
        $groups = array_unique($groups);

        return $this->getRestClient()->get("ndr/{$ndrId}", 'Ndr\Ndr', $groups);
    }

    /**
     * @param int $reportId
     *
     * @throws \RuntimeException if report is submitted
     *
     * @return Ndr
     *
     */
    protected function getNdrIfNotSubmitted($reportId, array $groups = [])
    {
        $report = $this->getNdr($reportId, $groups);
        if ($report->getSubmitted()) {
            throw new \RuntimeException('New deputy report already submitted and not editable.');
        }

        return $report;
    }

    /**
     * @return \AppBundle\Service\Mailer\MailFactory
     */
    protected function getMailFactory()
    {
        return $this->get('mail_factory');
    }

    /**
     * @return \AppBundle\Service\Mailer\MailSender
     */
    protected function getMailSender()
    {
        return $this->get('mail_sender');
    }

    /**
     * @param $route
     *
     * @return bool
     */
    protected function routeExists($route)
    {
        return $this->get('router')->getRouteCollection()->get($route) ? true : false;
    }

    /**
     * @return StepRedirector
     */
    protected function stepRedirector()
    {
        return $this->get('step_redirector');
    }

    /**
     * Get referer, only if matching an existing route
     *
     * @param  Request $request
     * @param  array   $excludedRoutes
     * @return string  referer URL, null if not existing or inside the $excludedRoutes
     */
    protected function getRefererUrlSafe(Request $request, array $excludedRoutes = [])
    {
        $refererUrlPath = str_replace('app_dev.php/', '', parse_url($request->headers->get('referer'), \PHP_URL_PATH));

        try {
            $routeParams = $this->get('router')->match($refererUrlPath);
        } catch (ResourceNotFoundException $e) {
            return null;
        }
        $routeName = $routeParams['_route'];
        if (in_array($routeName, $excludedRoutes)) {
            return null;
        }
        unset($routeParams['_route']);

        return $this->get('router')->generate($routeName, $routeParams);
    }

    /**
     * Generates client profile link. We cannot guarantee the passed client has access to current report
     * So we need to make another API call with the correct JMS groups
     * thus ensuring the client is retrieved with the current report.
     *
     * @param  Client     $client
     * @throws \Exception
     * @return string
     */
    protected function generateClientProfileLink(Client $client)
    {
        /** @var $client Client */
        $client = $this->getRestClient()->get('client/' . $client->getId(), 'Client', ['client', 'report-id', 'current-report']);

        $report = $client->getCurrentReport();

        if ($report instanceof Report) {
            // generate link
            return $this->generateUrl('report_overview', ['reportId' => $report->getId()]);
        }

        $this->get('logger')->log(
            'warning',
            'Client entity missing current report when trying to generate client profile link'
        );

        throw new \Exception('Unable to generate client profile link.');
    }

    protected function getSectionId()
    {
        return null;
    }
}
