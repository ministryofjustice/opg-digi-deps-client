<?php
namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AppBundle\Service\ReportStatusService;


class SafeguardController extends AbstractController{

    /**
     * @Route("/report/{reportId}/safeguarding", name="safeguarding")
     * @Template()
     */
    public function editAction($reportId)
    {
        $report = $this->getReport($reportId, [ "basic"]); // check the report is owned by this user.
        
        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }
        
        if ($report->getSafeguarding() == null) {
            $safeguarding = new EntityDir\Safeguarding();
        } else {
            $safeguarding = $report->getSafeguarding();
        }

        $request = $this->getRequest();
        $form = $this->createForm(new FormDir\SafeguardingType(), $safeguarding);

        $form->handleRequest($request);

        if($form->get('save')->isClicked() && $form->isValid()){
            $data = $form->getData();
            $data->setReport($report);
            $data->keepOnlyRelevantSafeguardingData();

            if ($safeguarding->getId() == null) {
                $this->get('restClient')->post('report/safeguarding' , $data, ['deserialise_group' => 'Default']);
            } else {
                $this->get('restClient')->put('report/safeguarding/'. $safeguarding->getId() ,$data, ['deserialise_group' => 'Default']);
            }
            $this->flashSuccess();

            //$t = $this->get('translator')->trans('page.safeguardinfoSaved', [], 'report-safeguarding');
            //$this->get('session')->getFlashBag()->add('action', $t);

            return $this->redirect($this->generateUrl('safeguarding', ['reportId'=>$reportId]) . "#pageBody");
        } 
        
        if ($form->get('save')->isClicked() && !$form->isValid()){
            $this->flashFailure();
        }

        $reportStatusService = new ReportStatusService($report, $this->get('translator'));
        
        return[ 'report' => $report,
                'reportStatus' => $reportStatusService,
                'form' => $form->createView(),
        ];
    }

}
