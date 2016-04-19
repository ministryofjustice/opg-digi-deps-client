<?php
namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AppBundle\Service\ReportStatusService;


class ActionController extends AbstractController{

    /**
     * @Route("/report/{reportId}/actions", name="actions")
     * @Template()
     */
    public function editAction($reportId)
    {
        $report = $this->getReport($reportId, [ "basic", "action"]); // check the report is owned by this user.
        
        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }
        
        if ($report->getAction() == null) {
            $action = new EntityDir\Action();
        } else {
            $action = $report->getAction();
        }

        $request = $this->getRequest();
        $form = $this->createForm(new FormDir\ActionType(), $action);

        $form->handleRequest($request);

        if($form->get('save')->isClicked() && $form->isValid()){
            $data = $form->getData();
            $data->setReport($report);

            $this->get('restClient')->put('report/'.$reportId.'/action' , $data);
            $this->flashSuccess();
            
            return $this->redirect($this->generateUrl('actions', ['reportId'=>$reportId]) . "#pageBody");
        } else if ($form->get('save')->isClicked() && !$form->isValid()) {
            $this->flashFailure();
        }

        $reportStatusService = new ReportStatusService($report, $this->get('translator'));
        
        return[ 'report' => $report,
                'reportStatus' => $reportStatusService,
                'form' => $form->createView(),
        ];
    }

}
