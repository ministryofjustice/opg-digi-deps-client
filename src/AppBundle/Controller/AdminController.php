<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Model\Email;
use AppBundle\Service\ApiClient;
use AppBundle\Service\MailSender;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
/**
* @Route("/admin")
*/
class AdminController extends Controller
{
    /**
     * @Route("/", name="admin_homepage")
     * @Template
     */
    public function indexAction(Request $request)
    {
        $apiClient = $this->get('apiclient'); /* @var $apiClient ApiClient */
        
        $form = $this->createForm(new FormDir\AddUserType([
            'roles' => $this->get('apiclient')->getEntities('Role', 'list_roles'),
            'roleIdEmptyValue' => $this->get('translator')->trans('roleId.defaultOption', [], 'admin')
        ]), new EntityDir\User());
        
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                // add user
                $response = $apiClient->postC('add_user', $form->getData(), [
                    'deserialise_group' => 'admin_add_user' //only serialise the properties modified by this form)
                ]);
                $user = $apiClient->getEntity('User', 'user/' . $response['id']);
                
                $activationEmail = $this->get('mailFactory')->createResetPasswordEmail($user);
                $this->get('mailSender')->send($activationEmail, [ 'text', 'html']);

                $request->getSession()->getFlashBag()->add(
                    'notice', 
                    'An activation email has been sent to the user.'
                );
                
                $this->get('auditLogger')->log(EntityDir\AuditLogEntry::ACTION_USER_ADD, $user);
                
                return $this->redirect($this->generateUrl('admin_homepage'));
            } 
        }
        
        return [
            'users'=>$this->get('apiclient')->getEntities('User', 'list_users'), 
            'form'=>$form->createView()
        ];
    }
    
    /**
     * @param User $user
     */
    private function sendActivationEmail(EntityDir\User $user)
    {
        // send activation link
        $emailConfig = $this->container->getParameter('email_send');
        $translator = $this->get('translator');
        $router = $this->get('router');

        $email = new Email();
        $viewParams = [
            'name' => $user->getFullName(),
            'domain' => $router->generate('homepage', [], true),
            'link' => $router->generate('user_activate', ['token'=> $user->getRegistrationToken()], true)
        ];
        $email->setFromEmail($emailConfig['from_email'])
            ->setFromName($translator->trans('activation.fromName',[], 'email'))
            ->setToEmail($user->getEmail())
            ->setToName($user->getFullName())
            ->setSubject($translator->trans('activation.subject',[], 'email'))
            ->setBodyHtml($this->renderView('AppBundle:Email:user-activate.html.twig', $viewParams))
            ->setBodyText($this->renderView('AppBundle:Email:user-activate.text.twig', $viewParams));

        $mailSender = $this->get('mailSender'); /* @var $mailSender MailSender */
        $mailSender->send($email,[ 'text', 'html']);
    }
    
    /**
     * @Route("/edit-user/{id}", name="admin_editUser")
     * @Method({"GET", "POST"})
     * @Template
     * 
     * @param Request $request
     */
    public function editUserAction($id)
    {
        $apiClient = $this->get('apiclient');
        
        $user = $apiClient->getEntity('User','find_user_by_id', [ 'parameters' => [ $id ] ]);
       
        if(empty($user)){
            throw new \Exception('User does not exists');
        }
        
        $form = $this->createForm(new FormDir\AddUserType([
            'roles' => $this->get('apiclient')->getEntities('Role', 'list_roles'),
            'roleIdEmptyValue' => $this->get('translator')->trans('roleId.defaultOption', [], 'admin')
        ]), $user );
        
        return [ 'form' => $form->createView(), 'action' => 'edit', 'id' => $id ];
    }
    
    /**
     * @Route("/delete-confirm/{id}", name="admin_delete_confirm")
     * @Method({"GET"})
     * @Template()
     * 
     * @param type $id
     */
    public function deleteConfirmAction($id)
    {
       $apiClient = $this->get('apiclient');
        
       $user = $apiClient->getEntity('User','find_user_by_id', [ 'parameters' => [ $id ] ]); 
       
       return [ 'user' => $user ];
    }
    
    /**
     * @Route("/delete/{id}", name="admin_delete")
     * @Method({"GET"})
     * @Template()
     * 
     * @param integer $id
     */
    public function deleteAction($id)
    {
        $apiClient = $this->get('apiclient');
        
        $user = $apiClient->getEntity('User','find_user_by_id', [ 'parameters' => [ $id ] ]); 
        
        $this->get('auditLogger')->log(EntityDir\AuditLogEntry::ACTION_USER_DELETE, $user);
        
        $apiClient->delete('delete_user_by_id',[ 'parameters' => ['adminId' => $this->getUser()->getId(), 'id' => $id ]]);
        
        return $this->redirect($this->generateUrl('admin_homepage'));
    }
}
