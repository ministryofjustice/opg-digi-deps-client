<?php
namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ClientController extends AbstractController
{
    /**
     * @Route("/user-account/client-edit", name="client_edit")
     * @Template()
     */
    public function editAction()
    {   
        $restClient = $this->get('restClient');
        
        $clients = $this->getUser()->getClients(); 
        $request = $this->getRequest();
        
        $client = !empty($clients)? $clients[0]: null;
        
        $report = new EntityDir\Report();
        $report->setClient($client);

        $allowedCot = $this->getAllowedCourtOrderTypeChoiceOptions([], 'arsort');
        $form = $this->createForm(new FormDir\ClientType($allowedCot), $client, [ 'action' => $this->generateUrl('client_edit', [ 'action' => 'edit'])]);
        $form->handleRequest($request);
        
        // edit client form
        if ($form->isValid()) {
            $clientUpdated = $form->getData();
            $clientUpdated->setId($client->getId());
            $restClient->put('client/upsert', $clientUpdated, [
                 'deserialise_group' => 'edit'
            ]);

            return $this->redirect($this->generateUrl('client_edit'));
        }
        
        return [
            'client' => $client,
            'form' => $form->createView(),
            'lastSignedIn' => $this->getRequest()->getSession()->get('lastLoggedIn'),
        ];

    }
    
    /**
     * @Route("/client/add", name="client_add")
     * @Template()
     */
    public function addAction()
    {
        $request = $this->getRequest();
        $restClient = $this->get('restClient');
        
        $clients = $this->getUser()->getClients();
        if (!empty($clients) && $clients[0] instanceof EntityDir\Client) {
            // update existing client
            $method = 'put';
            $client = $clients[0]; //existing client
        } else {
            // new client
            $method = 'post';
            $client = new EntityDir\Client();
            $client->addUser($this->getUser()->getId());
        }
  
        $allowedCot = $this->getAllowedCourtOrderTypeChoiceOptions([], 'arsort');
        $form = $this->createForm(new FormDir\ClientType($allowedCot), $client);
        
        $form->handleRequest($request);
        if ($form->isValid()) {
            $response = ($method === 'post') 
                      ? $restClient->post('client/upsert', $form->getData())
                      : $restClient->put('client/upsert', $form->getData());

            return $this->redirect($this->generateUrl('report_create', [ 'clientId' => $response['id']]));
        }
        return [ 'form' => $form->createView() ];
    }
}