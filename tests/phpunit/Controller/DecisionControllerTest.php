<?php
namespace AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

use Mockery as m;





class DecisionControllerTest extends WebTestCase
{
    protected $report;
    protected $client;
    protected $restClient;

    /**
     * @var Client
     */
    protected $frameworkBundleClient;

    public function setUp()
    {
        $this->frameworkBundleClient = static::createClient(['environment' => 'test','debug' => true]);

        $this->report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('isDue')->andReturn(true)
            ->shouldReceive('getDecisions')->andReturn([])
            ->shouldReceive('getSubmitted')->andReturn(false)
            ->shouldReceive('getClient')->andReturn(1)
            ->shouldReceive('getReasonForNoDecisions')->andReturn("")
            ->getMock();

        $this->client = m::mock('AppBundle\Entity\Client')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->getMock();

        $this->restClient = m::mock('AppBundle\Service\Client\RestClient')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('get')->withArgs(["report/1", "Report",[ 'query' => [ 'groups' => [ 'transactions', 'basic']]]])->andReturn($this->report)

            ->shouldReceive('get')->withArgs(['client/1', 'Client', [ 'query' => [ 'groups' => [ "basic"]]]])->andReturn($this->client)
            ->getMock();

        static::$kernel->getContainer()->set('restClient', $this->restClient);

    }
    
    
   
    /** @test */
    public function listActionRedirectToAddIfNoDecisionsAndNotDue() {
        
        $this->restClient->shouldReceive('get')->withArgs(['report/1/decisions', 'Decision[]'])->andReturn([]);
        
        $this->frameworkBundleClient->request( "GET","/report/1/decisions");
        $response =  $this->frameworkBundleClient->getResponse();
        $this->assertEquals( "/report/1/decisions/add", $response->getTargetUrl());
        
    }
    
    
}
