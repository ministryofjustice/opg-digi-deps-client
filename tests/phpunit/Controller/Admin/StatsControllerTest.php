<?php

use AppBundle\Controller\Admin\StatsController;
use AppBundle\Service\Client\RestClient;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class StatsControllerTest extends WebTestCase
{
    public function testDeputyStats()
    {
        $response = new DeputyQueryResponse();

        /** @var RestClient $restClient */
        $restClient = $this->prophesize(RestClient::class);
        $restClient->get(Argument::any(), Argument::any())->shouldBeCalled()->willReturn($response);

        $sut = new StatsController($restClient->reveal());
        self::assertInstanceOf(DeputyQueryResponse::class, $sut->getDeputyStats());
    }
}
