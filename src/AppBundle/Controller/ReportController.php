<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Client;
use AppBundle\Service\ApiClient;
use AppBundle\Form as FormDir;
use AppBundle\Entity as EntityDir;


class ReportController extends Controller
{
    /**
     * @Route("/report/create/{clientId}", name="report_create")
     * @Template()
     */
    public function createAction($clientId)
    {
        $request = $this->getRequest();
        $apiClient = $this->get('apiclient');
       
        $client = $this->getClient($clientId);
        
        $allowedCourtOrderTypes = $client->getAllowedCourtOrderTypes();
        
        //lets check if this  user already has another report, if not start date should be court order date
        $report = new EntityDir\Report();
        $report->setClient($client->getId());
        
        $reports = $client->getReports();
        
        if(empty($reports)){
            $report->setStartDate($client->getCourtDate());
        }
        
        //if client has property & affairs and health & welfare then give them property & affairs
        //else give them health and welfare
        if(count($allowedCourtOrderTypes) > 1){
            $report->setCourtOrderType(EntityDir\Report::PROPERTY_AND_AFFAIRS);
        }else{
            $report->setCourtOrderType($allowedCourtOrderTypes[0]);
        }
        
        $form = $this->createForm(new FormDir\ReportType(), $report,
                                  [ 'action' => $this->generateUrl('report_create', [ 'clientId' => $clientId ])]);
        $form->handleRequest($request);
       
        if($request->getMethod() == 'POST'){
            if($form->isValid()){
                $response = $apiClient->postC('add_report', $form->getData());
                return $this->redirect($this->generateUrl('report_overview', [ 'reportId' => $response['report'] ]));
            }
        }
        return [ 'form' => $form->createView() ];
    }
    
    /**
     * @Route("/report/{reportId}/overview", name="report_overview")
     * @Template()
     */
    public function overviewAction($reportId)
    {
        $report = $this->getReport($reportId);
        
        $client = $this->getClient($report->getClient());
        $request = $this->getRequest();
        
        $form = $this->createForm(new FormDir\ReportSubmitType($this->get('translator')));
        
        if($request->getMethod() == 'POST'){
            $form->handleRequest($request);
            
            if($form->isValid()){
                if($report->readyToSubmit()){
                    return $this->redirect($this->generateUrl('report_declaration', [ 'reportId' => $report->getId() ]));
                }
            }
        }
        
        return [
            'report' => $report,
            'client' => $client,
            'report_form_submit' => $form->createView()
        ];
    }
    
    /**
     * @Route("/report/{reportId}/contacts/{action}", name="contacts", defaults={ "action" = "list"})
     * @Template()
     */
    public function contactsAction($reportId,$action)
    {
        $report = $this->getReport($reportId);
        $client = $this->getClient($report->getClient());
        
        $request = $this->getRequest();
        
        $apiClient = $this->get('apiclient');
        $contacts = $apiClient->getEntities('Contact','get_report_contacts', [ 'parameters' => ['id' => $reportId ]]);
        
        //set up add contact form
        $contact = new EntityDir\Contact();
        
        //set up report submit form
        $form = $this->createForm(new FormDir\ContactType(), $contact);
        $reportSubmit = $this->createForm(new FormDir\ReportSubmitType($this->get('translator')));
        
        //set up add reason for no contact form
        $noContact = $this->createForm(new FormDir\ReasonForNoContactType());
        $noContact->setData([ 'reason' => $report->getReasonForNoContacts() ]);
        
        
        if($request->getMethod() == 'POST'){
            $form->handleRequest($request);
            $reportSubmit->handleRequest($request);
            $noContact->handleRequest($request);
           
            //check if contacts form was submitted
            if($form->get('save')->isClicked()){
                if($form->isValid()){
                    $contact = $form->getData();
                    $contact->setReport($reportId);
                    $apiClient->postC('add_report_contact', $contact);
                    
                    return $this->redirect($this->generateUrl('contacts', [ 'reportId' => $reportId ]));
                }
             
             //check if add reason for no contact form was submitted
            }elseif($noContact->get('saveReason')->isClicked()){
                if($noContact->isValid()){
                     $formData = $noContact->getData();
                     $report->setReasonForNoContacts($formData['reason']);
                     
                     $apiClient->putC('report/'.$report->getId(),$report);
                     
                     return $this->redirect($this->generateUrl('contacts',[ 'reportId' => $report->getId()]));
                }
               
            //the above 2 forms test false then submission was for the overall report submit
            }else{
                if($reportSubmit->isValid()){
                    if($report->readyToSubmit()){
                        return $this->redirect($this->generateUrl('report_declaration', [ 'reportId' => $report->getId() ]));
                    }
                }
            }
        }
        
        return [
            'form' => $form->createView(),
            'contacts' => $contacts,
            'action' => $action,
            'report' => $report,
            'client' => $client,
            'report_form_submit' => $reportSubmit->createView(),
            'no_contact' => $noContact->createView() ];
    }
    
   
    /**
     * @Route("/report/{reportId}/assets/{action}", name="assets", defaults={ "action" = "list"})
     * @Template()
     */
    public function assetsAction($reportId, $action)
    {
        $util = $this->get('util');
        $translator =  $this->get('translator');
        $dropdownKeys = $this->container->getParameter('asset_dropdown');
        $apiClient = $this->get('apiclient');
        $request = $this->getRequest();
        
        $titles = [];
        
        foreach($dropdownKeys as $key ){
            $translation = $translator->trans($key,[],'report-assets');
            $titles[$translation] = $translation;
        }

        $other = $titles['Other assets'];
        unset($titles['Other assets']);
        
        asort($titles);
        $titles['Other assets'] = $other;
        
        $report = $util->getReport($reportId, $this->getUser()->getId());
        $client = $util->getClient($report->getClient());

        $asset = new EntityDir\Asset();
        
        $form = $this->createForm(new FormDir\AssetType($titles),$asset);
        $reportSubmit = $this->createForm(new FormDir\ReportSubmitType($this->get('translator')));

        $assets = $apiClient->getEntities('Asset','get_report_assets', [ 'parameters' => ['id' => $reportId ]]);
        
        if($request->getMethod() == 'POST'){
            $form->handleRequest($request);
            $reportSubmit->handleRequest($request);

            if($form->get('save')->isClicked()){
                if($form->isValid()){
                    $asset = $form->getData();
                    $asset->setReport($reportId);

                    $apiClient->postC('add_report_asset', $asset);
                    return $this->redirect($this->generateUrl('assets', [ 'reportId' => $reportId ]));
                }
            }else{
         
                if($reportSubmit->isValid()){
                    if($report->readyToSubmit()){
                        return $this->redirect($this->generateUrl('report_declaration', [ 'reportId' => $report->getId() ]));
                    }
                }
            }
        }

        return [
            'report' => $report,
            'client' => $client,
            'action' => $action,
            'form'   => $form->createView(),
            'assets' => $assets,
            'report_form_submit' => $reportSubmit->createView()
        ];
    }

    
    /**
     * @Route("/report/{reportId}/declaration", name="report_declaration")
     * @Template()
     */
    public function declarationAction(Request $request, $reportId)
    {
        $util = $this->get('util');
        $report = $util->getReport($reportId, $this->getUser()->getId());
        if (!$report->isDue()) {
            throw new \RuntimeException("Report not ready for submission.");
        }
        $clients = $this->getUser()->getClients();
        $client = $clients[0];
        
        $form = $this->createForm(new FormDir\ReportDeclarationType());
        $form->handleRequest($request);
        if($form->isValid()){
            
            /**
             * //TODO
             * 
             * ADD REAL SUBMISSION OR SENDIN HERE
             * 
             */
            
            $request->getSession()->getFlashBag()->add(
                'notice', 
                $this->get('translator')->trans('page.reportSubmittedFlashMessage', [], 'report-declaration')
            );
            return $this->redirect($this->generateUrl('report_overview', ['reportId'=>$reportId]));
        }
        
        
        return [
            'report' => $report,
            'client' => $client,
            'form' => $form->createView(),
        ];
    }
    
    
    /**
     * @param integer $clientId
     *
     * @return Client
     */
    protected function getClient($clientId)
    {
        return $this->get('apiclient')->getEntity('Client','find_client_by_id', [ 'parameters' => [ 'id' => $clientId ]]);
    }
    
    /**
     * @param integer $reportId
     * 
     * @return Report
     */
    protected function getReport($reportId,array $groups = [ 'transactions'])
    {
        return $this->get('apiclient')->getEntity('Report', 'find_report_by_id', [ 'parameters' => [ 'userId' => $this->getUser()->getId() ,'id' => $reportId ], 'query' => [ 'groups' => $groups ]]);
    }
}