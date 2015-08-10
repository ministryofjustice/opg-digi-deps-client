<?php
namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SafeguardController extends Controller{
    
    /**
     * @Route("/report/{reportId}/safeguarding", name="safeguarding")
     * @Template()
     */
    public function safeguardingAction($reportId)
    {
        $util = $this->get('util');
        $report = $util->getReport($reportId);
        $request = $this->getRequest();
        
        $form = $this->createForm(new FormDir\SafeguardingType(), $report);
        

        if($request->getMethod() == 'POST'){
            $form->handleRequest($request);
            
            if($form->get('save')->isClicked()){
                if($form->isValid()){
                    $data = $form->getData();
                    $data->keepOnlyRelevantSafeguardingData();

                    $this->get('apiclient')->putC('report/' .  $report->getId(),$data);
                    $translator = $this->get('translator');

                    $this->get('session')->getFlashBag()->add('action', 'page.safeguardinfoSaved');

                    return $this->redirect($this->generateUrl('report_safeguarding', ['reportId'=>$reportId]) . "#pageBody");
                }
            }
        }

        return[ 'report' => $report,
                'client' => $util->getClient($report->getClient()),
                'form' => $form->createView(),
                'report_form_submit' => $this->get('reportSubmitter')->getFormView()
              ];
    }
    
}