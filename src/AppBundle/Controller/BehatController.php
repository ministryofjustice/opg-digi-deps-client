<?php
namespace AppBundle\Controller;

use GuzzleHttp\Client;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Exception\DisplayableException;

/**
 * @Route("/behat")
 */
class BehatController extends Controller
{
    private function checkIsBehatBrowser()
    {
        $expectedSecretParam = md5('behat-dd-' . $this->container->getParameter('secret'));
        $secret = $this->getRequest()->get('secret');
        
        if ($secret !== $expectedSecretParam) {
            
            // log access
            $this->get('logger')->error($this->getRequest()->getPathInfo(). ": $expectedSecretParam secret expected. 404 will be returned.");
            
            throw $this->createNotFoundException('Not found');
        }
    }
    
    /**
     * @Route("/{secret}/email-get-last")
     * @Method({"GET"})
     */
    public function getLastEmailAction()
    {
        $this->checkIsBehatBrowser();
        $content = $this->get('apiclient')->get('behat/email')->getBody();
        
        $contentArray = json_decode($content, 1);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new Response("Error decoding email: body:" . $content);
        }
        
        return new Response($contentArray['data']);
    }
    
    /**
     * @Route("/{secret}/email-reset")
     * @Method({"GET"})
     */
    public function resetAction()
    {
        $this->checkIsBehatBrowser();
        $content = $this->get('apiclient')->delete('behat/email')->getBody();
        
        return new Response($content);
    }
    
    /**
     * @Route("/{secret}/report/{reportId}/change-report-cot/{cot}")
     * @Method({"GET"})
     */
    public function reportChangeReportCot($reportId, $cot)
    {
        $this->checkIsBehatBrowser();
        $this->get('apiclient')->putC('report/'  .$reportId, json_encode([
            'cot_id' => $cot
        ]));
        
        return new Response('done');
    }
    
    /**
     * @Route("/{secret}/report/{reportId}/set-sumbmitted/{value}")
     * @Method({"GET"})
     */
    public function reportChangeSubmitted($reportId, $value)
    {
        $this->checkIsBehatBrowser();
        $this->get('apiclient')->putC('report/'  .$reportId, json_encode([
            'submitted' => ($value == 'true' || $value == 1)
        ]));
        
        return new Response('done');
    }
    
    
    /**
     * @Route("/{secret}/delete-behat-users")
     * @Method({"GET"})
     */
    public function deleteBehatUser()
    {
        $this->checkIsBehatBrowser();
        
        $this->get('apiclient')->delete('behat/users/behat-users');
        
        return new Response('done');
    }
    
    /**
     * @Route("/{secret}/report/{reportId}/change-report-end-date/{dateYmd}")
     * @Method({"GET"})
     */
    public function accountChangeReportDate($reportId, $dateYmd)
    {
        $this->get('apiclient')->putC('report/' . $reportId, json_encode([
            'end_date' => $dateYmd
        ]));
        
        return new Response('done');
    }
    
    /**
     * @Route("/{secret}/delete-behat-data")
     * @Method({"GET"})
     */
    public function resetBehatData()
    {
       return new Response('done');
    }
    
    /**
     * @Route("/{secret}/view-audit-log")
     * @Method({"GET"})
     * @Template()
     */
    public function viewAuditLogAction()
    {
        $this->checkIsBehatBrowser();
        
        $entities = $this->get('apiclient')->getEntities('AuditLogEntry', 'audit-log');
        
        return ['entries' => $entities];
    }
    
    /**
     * @Route("/textarea")
     */
    public function textAreaTestPage()
    {
        return $this->render('AppBundle:Behat:textarea.html.twig');    
    }
    
    /**
     * set token_date and registration_token on the user
     * 
     * @Route("/{secret}/user/{email}/token/{token}/token-date/{date}")
     * @Method({"GET"})
     */
    public function userSetToken($email, $token, $date)
    {
        $this->checkIsBehatBrowser();
        
        $user = $this->get('apiclient')->getEntity('User', 'user/get-user-by-email/' . $email);
        
        $this->get('apiclient')->putC('user/' . $user->getId(), $user, [
            'deserialise_group' => 'registrationToken',
        ]);
        
        $this->get('apiclient')->putC('user/' . $user->getId(), json_encode([
            'token_date' => $date,
            'registration_token' => $token
        ]));
        
        return new Response('done');
    }
    
    /**
     * @Route("/{secret}/check-app-params")
     * @Method({"GET"})
     */
    public function checkParamsAction()
    {
        $this->checkIsBehatBrowser();
        
        $content = $this->get('apiclient')->get('behat/check-app-params')->getBody();
        $contentDecoded = json_decode($content, 1);
        
        if ($contentDecoded['data'] !='valid') {
            throw new \RuntimeException('Invalid API params. Response: '.print_r($contentDecoded, 1));
        }
        
        return new Response($content);
    }
}
