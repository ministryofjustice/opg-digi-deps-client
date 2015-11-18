<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Service\Client\RestClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Service\ReportStatusService;



class Account2Controller extends AbstractController
{
    /**
     * @Route("/report/{reportId}/accounts/moneyin", name="accounts_moneyin")
     * @Template()
     */
    public function moneyinAction($reportId) {

        $restClient = $this->get('restClient'); /* @var $restClient RestClient */
        $request = $this->getRequest();

        $report = $this->getReport($reportId, [ 'transactions', 'basic']);
        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }
        $client = $this->getClient($report->getClient());

        $accounts = $report->getAccounts();

        $account = new EntityDir\Account();
        $account->setReportObject($report);
        
        return [
            'report' => $report,
            'client' => $client,
            'accounts' => $accounts,
            'subsection' => "moneyin"
        ];
        
        
    }

    /**
     * @Route("/report/{reportId}/accounts/moneyout", name="accounts_moneyout")
     * @Template()
     */
    public function moneyoutAction($reportId) {

        $restClient = $this->get('restClient'); /* @var $restClient RestClient */
        $request = $this->getRequest();

        $report = $this->getReport($reportId, [ 'transactions', 'basic']);
        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }
        $client = $this->getClient($report->getClient());

        $accounts = $report->getAccounts();

        $account = new EntityDir\Account();
        $account->setReportObject($report);

        return [
            'report' => $report,
            'client' => $client,
            'accounts' => $accounts,
            'subsection' => "moneyout"
        ];


    }

    /**
     * @Route("/report/{reportId}/accounts/banks", name="accounts_banks")
     * @Template()
     */
    public function banksAction($reportId) {

        $restClient = $this->get('restClient'); /* @var $restClient RestClient */
        $request = $this->getRequest();

        $report = $this->getReport($reportId, [ 'transactions', 'basic']);
        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }
        $client = $this->getClient($report->getClient());

        $accounts = $report->getAccounts();

        $account = new EntityDir\Account();
        $account->setReportObject($report);

        return [
            'report' => $report,
            'client' => $client,
            'accounts' => $accounts,
            'subsection' => 'banks'
        ];


    }

    /**
     * @Route("/report/{reportId}/accounts/balance", name="accounts_balance")
     * @Template()
     */
    public function balanceAction($reportId) {

        $restClient = $this->get('restClient'); /* @var $restClient RestClient */
        $request = $this->getRequest();

        $report = $this->getReport($reportId, [ 'transactions', 'basic']);
        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }
        $client = $this->getClient($report->getClient());

        $accounts = $report->getAccounts();

        $account = new EntityDir\Account();
        $account->setReportObject($report);

        return [
            'report' => $report,
            'client' => $client,
            'accounts' => $accounts,
            'subsection' => 'balance'
        ];


    }
    
    /**
     * @Route("/{reportId}/accounts", name="accounts")
     * @return RedirectResponse
     */
    public function accountsAction($reportId)
    {
        return $this->redirect($this->generateUrl('accounts_moneyin', [ 'reportId' => $reportId]));
    }
    
}
