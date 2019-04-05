<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Controller\AbstractController;
use AppBundle\Exception\DisplayableException;
use AppBundle\Form\Admin\ReportSubmissionDownloadFilterType;
use AppBundle\Form\Admin\StatsDateFilterType;
use AppBundle\Mapper\ReportSubmission\ReportSubmissionSummaryQuery;
use AppBundle\Entity\Stats\StatsQueryResponse;
use AppBundle\Service\Client\RestClient;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin/stats")
 */
class StatsController extends AbstractController
{
    const STATS_API_ENDPOINT = 'stats';
    const STATS_QUERY_TEMPLATE = '?from=%s&to=%s';
    /**
     * @Route("", name="admin_stats")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template
     * @param Request $request
     * @return array|Response
     */
    public function statsAction(Request $request)
    {
        $form = $this->createForm(ReportSubmissionDownloadFilterType::class , new ReportSubmissionSummaryQuery());
        $form->handleRequest($request);

        if ($form->isValid()) {
            try {

                $mapper = $this->get('mapper.report_submission_summary_mapper');
                $transformer = $this->get('transformer.report_submission_bur_fixed_width_transformer');

                $reportSubmissionSummaries = $mapper->getBy($form->getData());
                $downloadableData = $transformer->transform($reportSubmissionSummaries);

                return $this->buildResponse($downloadableData);

            } catch (\Exception $e) {
                throw new DisplayableException($e);
            }
        }

        $statsFilterForm = $this->createForm(StatsDateFilterType::class);
        $statsFilterForm->handleRequest($request);

        $stats = $this->getRestClient()->get(self::STATS_API_ENDPOINT, 'array');

        if ($statsFilterForm->isValid()) {
            $data = $statsFilterForm->getData();

            /** @var DateTime $from */
            $from = $data['from']->format('Y-m-d H:i:s');
            $to = $data['to']->format('Y-m-d H:i:s');

            $url = sprintf(self::STATS_API_ENDPOINT . self::STATS_QUERY_TEMPLATE, $from, $to);

            $stats = $this->getRestClient()->get($url, 'array');
        }

        return [
            'form' => $form->createView(),
            'statsFilterForm' => $statsFilterForm->createView(),
            'stats' => $stats
        ];
    }

    /**
     * @param $csvContent
     * @return Response
     */
    private function buildResponse($csvContent)
    {
        $response = new Response($csvContent);
        $response->headers->set('Content-Type', 'application/octet-stream');

        $attachmentName = sprintf('cwsdigidepsopg00001%s.dat', date('YmdHi'));
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $attachmentName . '"');

        $response->sendHeaders();

        return $response;
    }
}
