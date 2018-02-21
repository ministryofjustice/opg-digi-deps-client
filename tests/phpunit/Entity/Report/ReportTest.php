<?php

namespace AppBundle\Entity\Report;

use Mockery as m;

class ReportTest extends \PHPUnit_Framework_TestCase
{
    /** @var Report $report */
    private $report;

    /** @var Account $account */
    private $account;

    protected function setUp()
    {
        $this->report = new Report();
        $this->account = m::mock('AppBundle\Entity\Report\BankAccount');
    }

    public function tearDown()
    {
        m::close();
    }

    /**
     * @dataProvider isDueProvider
     * @test
     */
    public function isDue($endDateModifier, $expected)
    {
        $endDate = new \DateTime();
        $endDate->modify($endDateModifier);
        $this->report->setEndDate($endDate);

        $lastMidnight = new \DateTime();
        $lastMidnight->setTime(0, 0, 0);

        $actual = $this->report->isDue();

        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function hasMoneyInWhenThereIsMoneyIn()
    {
        $transaction1 = m::mock('AppBundle\Entity\Transaction')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getAmounts')->andReturn([100])
            ->getMock();

        $transaction2 = m::mock('AppBundle\Entity\Transaction')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getAmounts')->andReturn([null])
            ->getMock();

        $this->report->setMoneyTransactionsIn([$transaction1, $transaction2]);

        $this->assertEquals(true, $this->report->hasMoneyIn());
    }

    /** @test */
    public function hasMoneyInWhenThereIsNoMoneyIn()
    {
        $this->report->setMoneyTransactionsIn([]);
        $this->assertEquals(false, $this->report->hasMoneyIn());

        $transaction1 = m::mock(MoneyTransfer::class)
            ->shouldIgnoreMissing(true)
            ->getMock();

        $this->report->setMoneyTransactionsIn([$transaction1]);

        $this->assertEquals(true, $this->report->hasMoneyIn());
    }

    public function getCountValidTotalsProvider()
    {
        return [
            [[], [], 0],
            [['in1' => null], ['out1' => null], 0],
            [['in1' => 123], [], 1],
            [['in1' => 0], [], 1],
            [[], ['out1' => 123], 1],
            [[], ['out1' => 0], 1],
            [['in1' => 123], ['out1' => 123], 2],
            [['in1' => 0, 'in2' => null], ['out1' => 123, 'out2' => null], 2],
        ];
    }

    public function isDueProvider()
    {
        return [
            ['-1 year', true],
            ['-1 day', true],
            ['+0 days', true],

            ['+1 day', false],
            ['+7 day', false],
            ['+14 day', false],
            ['+1 month', false],
            ['+1 year', false],
        ];
    }

    public function testgetBankAccountsIncomplete()
    {
        $closingBalancePositive = m::mock(BankAccount::class, ['getClosingBalance'=>1, 'setReport'=>'']);
        $closingBalanceZero = m::mock(BankAccount::class, ['getClosingBalance'=>0, 'setReport'=>'']);
        $closingBalanceMissing = m::mock(BankAccount::class, ['getClosingBalance'=>null, 'setReport'=>'']);

        $this->report = new Report();
        $this->report->setBankAccounts([$closingBalancePositive, $closingBalanceZero, $closingBalanceMissing]);

        $this->assertCount(1, $this->report->getBankAccountsIncomplete(), 'only null account expected');
    }

    public function dueDateDiffDaysProvider()
    {
        return [
            [null, new \DateTime('2017-02-21'), null],
            [new \DateTime('2017-02-21'), new \DateTime('2017-02-21'), 0],
            [new \DateTime('2017-02-21'), new \DateTime('2017-02-21 23:59'), 0],
            [new \DateTime('2017-02-21'), new \DateTime('2017-02-21 00:01'), 0],
            [new \DateTime('2017-02-21'), new \DateTime('2017-02-22'), -1],
            [new \DateTime('2017-02-21'), new \DateTime('2017-02-20'), 1],
        ];
    }

    /**
     * @dataProvider dueDateDiffDaysProvider
     */
    public function testgetDueDateDiffDays($dueDate, $currentDate, $expected)
    {
        $report = m::mock(Report::class . '[getDueDate]');
        $report->shouldReceive('getDueDate')->andReturn($dueDate);

        $actual = $report->getDueDateDiffDays($currentDate);
        $this->assertEquals($expected, $actual);
    }

    public function isOtherFeesSectionCompleteProvider()
    {
        return [
            [null, [], false],
            ['yes', [], false],
            [null, ['exp1'], false],
            ['no', [], true],
            ['no', ['exp1'], true],
            ['yes', ['exp1'], true],
            ['no', [], true],
        ];
    }

    /**
     * @dataProvider isOtherFeesSectionCompleteProvider
     */
    public function testisOtherFeesSectionComplete($paidForAnything, $getExpenses, $expected)
    {
        $report = m::mock(Report::class . '[getPaidForAnything, getExpenses]');
        $report->shouldReceive('getPaidForAnything')->andReturn($paidForAnything);
        $report->shouldReceive('getExpenses')->andReturn($getExpenses);

        $this->assertEquals($expected, $report->isOtherFeesSectionComplete());
    }

    public function reportTypeTransSuffixProvider()
    {
        return [
            // Lay deputies
            ['NDR', ''],
            ['102', ''],
            ['103', ''],
            ['104', '-104'],
            ['103-4', '-4'],
            ['102-4', '-4'],

            // PA
            ['102-6', ''],
            ['103-6', ''],
            ['104-6', ''-104],
            ['102-4-6', '-4'],
            ['103-4-6', '-4'],

            // Professional
            ['102-5', ''],
            ['103-5', ''],
            ['104', '-104'],
            ['102-4-5', '-4'],
            ['103-4-5', '-4'],
        ];
    }

    /**
     * @dataProvider reportTypeTransSuffixProvider
     */
    public function testGet104TransSuffix($reportType, $expectedSuffix)
    {
        $report = new Report();

        $report->setType($reportType);

        $this->assertEquals($expectedSuffix, $report->get104TransSuffix());
    }

    public function testgetAssetsTotalsSummaryPage()
    {
        $report = new Report();
        $report->setAssets([
            m::mock(AssetProperty::class, ['getTitle'=>'', 'getValueTotal'=>100000]),
            m::mock(AssetOther::class, ['getTitle'=>'Unit trusts', 'getValueTotal'=>10]),
            m::mock(AssetOther::class, ['getTitle'=>'National Savings certificates', 'getValueTotal'=>20]),
            m::mock(AssetOther::class, ['getTitle'=>'Stocks and shares', 'getValueTotal'=>30]),
            m::mock(AssetOther::class, ['getTitle'=>'Premium Bonds', 'getValueTotal'=>40]),

            m::mock(AssetOther::class, ['getTitle'=>'Artwork', 'getValueTotal'=>1]),
            m::mock(AssetOther::class, ['getTitle'=>'Antiques', 'getValueTotal'=>2]),
            m::mock(AssetOther::class, ['getTitle'=>'Jewellery', 'getValueTotal'=>3]),
            m::mock(AssetOther::class, ['getTitle'=>'Assets held outside England and Wales', 'getValueTotal'=>4]),
            m::mock(AssetOther::class, ['getTitle'=>'Other valuable assets', 'getValueTotal'=>5])
        ]);

        $this->assertEquals(100000, $report->getAssetsTotalsSummaryPage('property'));
        $this->assertEquals(10 + 20 + 30 + 40, $report->getAssetsTotalsSummaryPage('cash'));
        $this->assertEquals(1+2+3+4+5, $report->getAssetsTotalsSummaryPage('other'));
    }
}
