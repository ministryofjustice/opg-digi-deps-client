<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\Client;
use Mockery as m;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    /** @var Client $client */
    private $client;

    protected function setUp()
    {
        $this->client = new Client();
    }

    public function tearDown()
    {
        m::close();
    }

    /**
     * Data provider, expected reporting start date from a given court date
     */
    public function courtDateExpectedStartDateProvider()
    {
        $currentYear = date('Y');
        return [
            [new \DateTime('2000-01-01'), new \DateTime($currentYear-1 . '-01-01')],
            [new \DateTime('2000-12-31'), new \DateTime(($currentYear -1) . '-12-31')],
            [new \DateTime('2003-09-30'), new \DateTime(($currentYear-1) . '-09-30')],
            [new \DateTime('2015-03-31'), new \DateTime(($currentYear-1) . '-03-31')],
            [new \DateTime('2016-02-29'), new \DateTime(($currentYear-1) . '-03-01')],
            [new \DateTime('2017-03-01'), new \DateTime(($currentYear-1) . '-03-01')]
        ];
    }

    /**
     * Data provider, expected reporting end date from a given court date
     */
    public function courtDateExpectedEndDateProvider()
    {
        $currentYear = date('Y');

        return [
            [new \DateTime('2000-01-01'), new \DateTime(($currentYear-1) . '-12-31')],
            [new \DateTime('2000-12-31'), new \DateTime(($currentYear) . '-12-30')],
            [new \DateTime('2003-09-30'), new \DateTime(($currentYear) . '-09-29')],
            [new \DateTime('2015-03-31'), new \DateTime(($currentYear) . '-03-30')],
            [new \DateTime('2016-02-29'), new \DateTime(($currentYear) . '-02-28')],
            [new \DateTime('2017-03-01'), new \DateTime(($currentYear) . '-02-28')]
        ];
    }

    /**
     * @dataProvider courtDateExpectedStartDateProvider
     */
    public function testGetExpectedStartDate($courtDate, $expected)
    {
        $this->client->setCourtDate($courtDate);
        $this->assertEquals(
            $expected->format('d/m/Y'),
            $this->client->getExpectedReportStartDate()->format('d/m/Y')
        );
    }

    /**
     * @dataProvider courtDateExpectedEndDateProvider
     */
    public function testGetExpectedEndDate($courtDate, $expected)
    {
        $this->client->setCourtDate($courtDate);
        $this->assertEquals(
            $expected->format('d/m/Y'),
            $this->client->getExpectedReportEndDate()->format('d/m/Y')
        );
    }
}
