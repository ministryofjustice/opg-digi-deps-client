<?php

namespace AppBundle\Resources\views\VisitsCare;

use AppBundle\Entity as EntityDir;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DomCrawler\Crawler;
use AppBundle\Form as FormDir;
use Mockery as m;

class VisitsCareTest extends WebTestCase
{
    /** @var  \Symfony\Bundle\TwigBundle\TwigEngine */
    private $twig;

    /** @var  \Symfony\Bundle\FrameworkBundle\ContainerInterface */
    private $container;

    public function setUp()
    {
        $client = static::createClient(['environment' => 'test', 'debug' => false]);
        $this->container = $client->getContainer();

        $this->twig = $this->container->get('templating');

        $request = new Request();
        $request->create('/report/1/visits-care');
        $this->container->enterScope('request');
        $this->container->set('request', $request, 'request');
        $this->container->get('request_stack')->push(Request::createFromGlobals());
    }

    public function tearDown()
    {
        m::close();
        $this->container->leaveScope('request');
    }

    /** @test */
    public function showContinueWhenVisitsCareSaved()
    {
        $visitsCare = new EntityDir\Report\VisitsCare();

        // mock data
        $report = m::mock('AppBundle\Entity\Report\Report')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('getId')->andReturn(1)
                ->shouldReceive('isDue')->andReturn(false)
                ->shouldReceive('getVisitsCare')->andReturn($visitsCare)
                ->getMock();

        $client = m::mock('AppBundle\Entity\Client')
                ->shouldIgnoreMissing(true)
                ->getMock();

        $form = $this->createForm(new FormDir\Report\VisitsCareType(), $visitsCare);

        $html = $this->twig->render('AppBundle:Report/VisitsCare:edit.html.twig', [
            'report' => $report,
            'form' => $form->createView(),
            'client' => $client,
        ]);

        $crawler = new Crawler($html);

        $this->assertCount(1, $crawler->filter('nav.pagination .previous'));
        $this->assertEquals('/report/1/contacts', $crawler->filter('nav.pagination .previous a')->eq(0)->attr('href'));
        $this->assertEquals('Contacts', $crawler->filter('nav.pagination .previous .pagination-part-title')->eq(0)->text());

        $this->assertCount(1, $crawler->filter('nav.pagination .next'));
        $this->assertEquals('/report/1/accounts', $crawler->filter('nav.pagination .next a')->eq(0)->attr('href'));
        $this->assertEquals('Accounts', $crawler->filter('nav.pagination .next .pagination-part-title')->eq(0)->text());
    }

    public function createForm($type, $data = null, array $options = array())
    {
        return $this->container->get('form.factory')->create($type, $data, $options);
    }
}
