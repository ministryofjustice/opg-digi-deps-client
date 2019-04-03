<?php

namespace Tests\Controller\Admin;

use AppBundle\Controller\Admin\StatsController;
use AppBundle\Service\Client\RestClient;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AppBundle\Entity\Stats\StatsQueryResponse;

class StatsControllerTest extends WebTestCase
{
    /**
     * @group acs
     */
    public function testGetStats()
    {
        $response = new StatsQueryResponse();
        $response->setDeputyCount(3);

        $expectedJson = json_encode(['deputyCount' => 3], 1);

        /** @var RestClient $restClient */
        $restClient = $this->prophesize(RestClient::class);
        $restClient->get(Argument::any(), Argument::any())->shouldBeCalled()->willReturn($response);

        $sut = new StatsController($restClient->reveal());
        $response = $sut->getStats(Argument::any());

        self::assertJsonStringEqualsJsonString($expectedJson, $response);
    }
}
