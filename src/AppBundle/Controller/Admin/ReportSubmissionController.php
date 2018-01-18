<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Service\File\MultiDocumentZipFileCreator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin/documents")
 */
class ReportSubmissionController extends AbstractController
{
    const ACTION_DOWNLOAD = 'download';
    const ACTION_ARCHIVE = 'archive';

    private $allowedPostActions = [
        self::ACTION_DOWNLOAD,
        self::ACTION_ARCHIVE,
    ];

    /**
     * @Route("/list", name="admin_documents")
     * @Template
     */
    public function indexAction(Request $request)
    {
        $this->processPost($request);
        $currentFilters = self::getFiltersFromRequest($request);
        $ret = $this->getRestClient()->get('/report-submission?' . http_build_query($currentFilters), 'array');
        $records = $this->getRestClient()->arrayToEntities(EntityDir\Report\ReportSubmission::class . '[]', $ret['records']);

        return [
            'filters' => $currentFilters,
            'records' => $records,
            'postActions' => $this->allowedPostActions,
            'counts'  => [
                'new'      => $ret['counts']['new'],
                'archived' => $ret['counts']['archived'],
            ],
        ];
    }

    /**
     * Process a post
     *
     * @param Request $request request
     *
     * @return void
     */
    private function processPost(Request $request) {
        if ($request->isMethod('POST')){
            if (empty($request->request->get('checkboxes'))) {
                $request->getSession()->getFlashBag()->add('error', 'Please select at least one row');
                return;
            }

            $checkedBoxes = array_keys($request->request->get('checkboxes'));
            $action = $request->request->get('multiAction');

            if (in_array($action, $this->allowedPostActions)) {
                $totalChecked = count($checkedBoxes);

                switch ($action) {
                    case self::ACTION_ARCHIVE:
                        $this->processArchive($checkedBoxes);
                        $request->getSession()->getFlashBag()->add('notice', $totalChecked . ' documents archived');
                        break;
                    case self::ACTION_DOWNLOAD:
                        $this->processDownload($request, $checkedBoxes);
                        break;
                }
            }
        }
    }

    /**
     * Archive multiple documents based on the supplied ids
     *
     * @param array $checkedBoxes ids selected by the user
     *
     * @return void
     */
    private function processArchive($checkedBoxes)
    {
        foreach($checkedBoxes as $reportSubmissionId) {
            $this->getRestClient()->put("report-submission/{$reportSubmissionId}", ['archive'=>true]);
        }
    }

    /**
     * Download multiple documents based on the supplied ids
     *
     * @param Request $request request
     * @param array $checkedBoxes ids selected by the user
     *
     * @return Response
     */
    private function processDownload(Request $request, $checkedBoxes)
    {
        $reportSubmissions = [];

        try {
            foreach ($checkedBoxes as $reportSubmissionId) {
                $reportSubmissions[] = $this->getRestClient()->get("/report-submission/{$reportSubmissionId}", 'Report\\ReportSubmission');
            }

            $zipFileCreator = new MultiDocumentZipFileCreator($this->get('s3_storage'), $reportSubmissions);
            $filename = $zipFileCreator->createZipFile();

            // send ZIP to user
            $response = new Response();
            $response->headers->set('Pragma', 'public');
            $response->headers->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
            $response->headers->set('Expires', '0');
            $response->headers->set('Content-type', 'application/octet-stream');
            $response->headers->set('Content-Description', 'File Transfer');
            $response->headers->set('Content-Disposition', 'attachment; filename="' . basename($filename) . '";');
            // currently disabled as behat goutte driver gets a corrupted file with this setting
            //$response->headers->set('Content-Length', filesize($filename));
            $response->sendHeaders();
            $response->setContent(readfile($filename));

            $zipFileCreator->cleanUp();

            return $response;
        } catch (\Exception $e) {
            $zipFileCreator->cleanUp();
            $request->getSession()->getFlashBag()->add('error', 'Cannot download documents. Details: ' . $e->getMessage());
        }
    }

    /**
     * @param  Request $request
     * @return array
     */
    private static function getFiltersFromRequest(Request $request)
    {
        return [
            'q'      => $request->get('q'),
            'status' => $request->get('status', 'new'), // new | archived
            'limit'             => $request->query->get('limit') ?: 15,
            'offset'            => $request->query->get('offset') ?: 0,
            'created_by_role'   => $request->get('created_by_role'),
            'orderBy'           => $request->get('orderBy', 'createdOn'),
            'order'             => $request->get('order', 'ASC')
        ];
    }
}
