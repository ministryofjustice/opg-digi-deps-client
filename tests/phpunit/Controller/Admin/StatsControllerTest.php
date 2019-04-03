<?php

namespace Tests\Controller\Admin;

use AppBundle\Controller\Admin\StatsController;
use AppBundle\Service\Client\RestClient;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AppBundle\Model\DeputyQueryResponse;

class StatsControllerTest extends WebTestCase
{
    /**
     * @group acs
     */
    public function testDeputyStats()
    {
        $response = new DeputyQueryResponse();
        $response->setDeputiesCount(3);

        /** @var RestClient $restClient */
        $restClient = $this->prophesize(RestClient::class);
        $restClient->get(Argument::any(), Argument::any())->shouldBeCalled()->willReturn($response);

        $sut = new StatsController($restClient->reveal());
        $response = $sut->getDeputyStats(Argument::any);

        $expectedJson = json_encode(['deputyCount' => 3]);

        self::assertJsonStringEqualsJsonString($expectedJson, $response);
    }
}
