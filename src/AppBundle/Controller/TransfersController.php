<?php
namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Service\Client\RestClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;


class TransfersController extends AbstractController
{

    /**
     * @Route("/report/{reportId}/transfers/edit", name="transfers")
     * @param integer $reportId
     * @Template()
     * @return array
     */
    public function transfersAction($reportId)
    {
        $report = $this->getReport($reportId, ['basic', 'client', 'accounts']);
        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }

        return [
            'report' => $report,
            'subsection' => 'moneyin'
        ];
    }

    /**
     * @Route("/report/{reportId}/transfers", name="transfers_get_json")
     * @Method({"GET"})
     * @param Request $request
     * @param integer $reportId
     * return JsonResponse
     */
    public function transfersGetJson(Request $request, $reportId)
    {
        $data = $this->getRestClient()->get("report/{$reportId}", 'array', [ 'query' => [ 'groups' => 'transfers']]);

        return new JsonResponse(array('transfers' => $data['money_transfers']));
    }

    /**
     * @Route("/report/{reportId}/transfers", name="transfers_add_json")
     * @Method({"POST"})
     * @param Request $request
     * @param integer $reportId
     * return JsonResponse
     */
    public function transfersSaveJson(Request $request, $reportId)
    {
        $data = json_decode($request->getContent(), true)['transfer'];
        
        $transferUpdated = $this->get('restClient')->post('report/' . $reportId . '/money-transfers', $data);
        
        return new JsonResponse($transferUpdated);
    }


    /**
     * @Route("/report/{reportId}/transfers/{transferId}", name="transfers_update_json")
     * @Method({"PUT"})
     * @param Request $request
     * @param integer $reportId
     * @param integer $transferId
     * return JsonResponse
     */
    public function transfersUpdateJson(Request $request, $reportId, $transferId)
    {
       $data = json_decode($request->getContent(), true)['transfer'];
       
        $transferUpdated = $this->get('restClient')->put('report/' . $reportId . '/money-transfers/' . $transferId, $data);
        
        return new JsonResponse($transferUpdated);
    }

    /**
     * @Route("/report/{reportId}/transfers/{transferId}", name="transfers_delete_json")
     * @Method({"DELETE"})
     * @param Request $request
     * @param integer $reportId
     * @param integer $transferId
     * return JsonResponse
     */
    public function transfersDeleteJson(Request $request, $reportId, $transferId)
    {
        return new JsonResponse([]);
    }
}
