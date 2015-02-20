<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Form\ClientType;
use AppBundle\Entity\Client;


/**
 * @Route("/client")
 */
class ClientController extends Controller
{
    /**
     * @Route("/add", name="client_add")
     * @Template()
     */
    public function addAction()
    {
        $request = $this->getRequest();
        $apiClient = $this->get('apiclient');
        
        $client = new Client();
        $client->setUser($this->getUser()->getId());
        
        $form = $this->createForm(new ClientType($apiClient), $client);
        $form->handleRequest($request);
        
        if($request->getMethod() == 'POST'){
            if($form->isValid()){
                $apiClient->postC('add_client', $form->getData());
                return $this->redirect($this->generateUrl('report_create'));
            }
        }
        return [ 'form' => $form->createView() ];
    }
}