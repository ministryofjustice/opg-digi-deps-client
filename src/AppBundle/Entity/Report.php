<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * @JMS\XmlRoot("report")
 * @JMS\ExclusionPolicy("none")
 * @Assert\Callback(methods={"isValidEndDate", "isValidDateRange"})
 */
class Report
{
    /**
     * 
     */
    const PERSONAL_WELFARE = 1;

    /**
     * same sections as personal welfare, + accounts and assets.
     */
    const PROPERTY_AND_AFFAIRS = 2;

    /**
     * @JMS\Type("integer")
     * @JMS\Groups({"safeguarding"})
     *
     * @var int
     */
    private $id;

    /**
     * @Assert\NotBlank( message="report.startDate.notBlank")
     * @Assert\Date( message="report.startDate.invalidMessage" )
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @JMS\Groups({"startEndDates"})
     *
     * @var \DateTime
     */
    private $startDate;

    /**
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @JMS\Groups({"startEndDates"})
     * @Assert\NotBlank( message="report.endDate.notBlank" )
     * @Assert\Date( message="report.endDate.invalidMessage" )
     *
     * @var \DateTime
     */
    private $endDate;

    /**
     * @var \DateTime
     * @JMS\Accessor(getter="getSubmitDate", setter="setSubmitDate")
     * @JMS\Type("DateTime")
     * @JMS\Groups({"submit"})
     */
    private $submitDate;

    /**
     * @JMS\Type("AppBundle\Entity\Client")
     *
     * @var Client
     */
    private $client;

    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $courtOrderTypeId;

    /**
     * @JMS\Exclude
     *
     * @var string
     */
    private $period;

    /**
     * @JMS\Type("array<AppBundle\Entity\Account>")
     *
     * @var Account[]
     */
    private $accounts;

    /**
     * @JMS\Type("array<AppBundle\Entity\MoneyTransfer>")
     *
     * @var MoneyTransfer[]
     */
    private $moneyTransfers;

    /**
     * @JMS\Type("array<AppBundle\Entity\Contact>")
     *
     * @var Contact[]
     */
    private $contacts;

    /**
     * @JMS\Type("array<AppBundle\Entity\Asset>")
     *
     * @var Asset[]
     */
    private $assets;

    /**
     * @JMS\Type("array<AppBundle\Entity\Decision>")
     *
     * @var Decision[]
     */
    private $decisions;

    /**
     * @JMS\Type("AppBundle\Entity\Safeguarding")
     *
     * @var \AppBundle\Entity\Safeguarding
     */
    private $safeguarding;

    /**
     * @JMS\Type("AppBundle\Entity\Action")
     *
     * @var \AppBundle\Entity\Action
     */
    private $action;

    /**
     * @JMS\Type("AppBundle\Entity\MentalCapacity")
     *
     * @var MentalCapacity
     */
    private $mentalCapacity;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $reasonForNoContacts;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $reasonForNoDecisions;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $noAssetToAdd;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $noTransfersToAdd;

    /**
     * @JMS\Type("boolean")
     * @JMS\Groups({"submit"})
     * @Assert\True(message="report.submissionExceptions.submitted", groups={"submitted"})
     * @Assert\False(message="report.submissionExceptions.notSubmitted", groups={"notSubmitted"})
     * 
     * @var bool
     */
    private $submitted;

    /**
     * @JMS\Type("boolean")
     * @JMS\Groups({"reviewed"})
     * @Assert\True(message="report.submissionExceptions.reviewedAndChecked", groups={"reviewedAndChecked"})
     * 
     * @var bool
     */
    private $reviewed;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"furtherInformation"})
     *
     * @var string
     */
    private $furtherInformation;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $reportSeen;

    /** @var bool
     * @JMS\Type("boolean")
     * @Assert\True(message="report.agree", groups={"declare"} )
     */
    private $agree;

    /** 
     * @var string
     *              
     * @JMS\Type("string")
     * @JMS\Groups({"basic","submit"})
     * @Assert\NotBlank(message="report.agreedBehalfDeputy.notBlank", groups={"declare"} )
     */
    private $agreedBehalfDeputy;

    /** 
     * @var string
     *              
     * @JMS\Type("string")
     * @JMS\Groups({"basic","submit"})
     * @Assert\NotBlank(message="report.agreedBehalfDeputyExplanation.notBlank", groups={"declare-explanation"} )
     */
    private $agreedBehalfDeputyExplanation;

    /**
     * @JMS\Type("array<AppBundle\Entity\Transaction>")
     * @JMS\Groups({"transactionsIn"})
     *
     * @var Transaction[]
     */
    private $transactionsIn;

    /**
     * @JMS\Type("array<AppBundle\Entity\Transaction>")
     * @JMS\Groups({"transactionsOut"})
     *
     * @var Transaction[]
     */
    private $transactionsOut;

    /**
     * @JMS\Type("double")
     *
     * @var float
     */
    private $moneyInTotal;

    /**
     * @JMS\Type("double")
     *
     * @var float
     */
    private $moneyOutTotal;

    /**
     * @JMS\Type("double")
     *
     * @var float
     */
    private $accountsOpeningBalanceTotal;

    /**
     * @JMS\Type("double")
     *
     * @var float
     */
    private $accountsClosingBalanceTotal;

    /**
     * @JMS\Type("double")
     *
     * @var float
     */
    private $calculatedBalance;

    /**
     * @JMS\Type("double")
     *
     * @var float
     */
    private $totalsOffset;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $totalsMatch;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"balance_mismatch_explanation"})
     *
     * @var string
     */
    private $balanceMismatchExplanation;

    /**
     * @JMS\Type("array<AppBundle\Entity\Debt>")
     * @JMS\Groups({"debts"})
     *
     * @var ArrayCollection
     */
    private $debts;


    /**
     * @JMS\Type("string")
     * @JMS\Groups({"debts"})
     *
     * @var decimal
     */
    private $debtsTotalAmount;

    /**
     * @return int $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return \AppBundle\Entity\Report
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     * 
     * @return \AppBundle\Entity\Report
     */
    public function setStartDate(\DateTime $startDate = null)
    {
        if ($startDate instanceof \DateTime) {
            $startDate->setTime(0, 0, 0);
        }
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @return \DateTime $endDate
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Return the date 8 weeks after the end date.
     * 
     * @return string $dueDate
     */
    public function getDueDate()
    {
        if (!$this->endDate instanceof \DateTime) {
            return;
        }
        $dueDate = clone $this->endDate;
        $dueDate->modify('+8 weeks');

        return $dueDate;
    }

    /**
     * Get submitDate.
     *
     * @return \DateTime
     */
    public function getSubmitDate()
    {
        if ($this->submitted) {
            $submitDate = $this->submitDate;
        } else {
            $submitDate = null;
        }

        return $submitDate;
    }

    /**
     * @param \DateTime $submitDate
     *
     * @return \AppBundle\Entity\Report
     */
    public function setSubmitDate(\DateTime $submitDate = null)
    {
        $this->submitDate = $submitDate;

        return $this;
    }

    /**
     * @param \DateTime $endDate
     *
     * @return \AppBundle\Entity\Report
     */
    public function setEndDate(\DateTime $endDate = null)
    {
        if ($endDate instanceof \DateTime) {
            $endDate->setTime(23, 59, 59);
        }
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Return string representation of the start-end date period
     * e.g. 2004 to 2005.
     * 
     * @return string $period
     */
    public function getPeriod()
    {
        if (!empty($this->period)) {
            return $this->period;
        }

        if (empty($this->startDate)) {
            return $this->period;
        }

        $startDateStr = $this->startDate->format('Y');
        $endDateStr = $this->endDate->format('Y');

        if ($startDateStr != $endDateStr) {
            $this->period = $startDateStr.' to '.$endDateStr;

            return $this->period;
        }
        $this->period = $startDateStr;

        return $this->period;
    }

    /**
     * @return int $client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param int $client
     *
     * @return \AppBundle\Entity\Report
     */
    public function setClient(Client $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return int
     */
    public function getCourtOrderTypeId()
    {
        return $this->courtOrderTypeId;
    }

    /**
     * @param int $courtOrderTypeId
     *
     * @return \AppBundle\Entity\Report
     */
    public function setCourtOrderTypeId($courtOrderTypeId)
    {
        $this->courtOrderTypeId = $courtOrderTypeId;

        return $this;
    }

    /**
     * @return Account[]
     */
    public function getAccounts()
    {
        return $this->accounts;
    }

    /**
     * @return MoneyTransfer[]
     */
    public function getMoneyTransfers()
    {
        return $this->moneyTransfers;
    }

    /**
     * @return MoneyTransfer
     */
    public function getMoneyTransferWithId($id)
    {
        foreach ($this->moneyTransfers as $t) {
            if ($t->getId() == $id) {
                return $t;
            }
        }

        return;
    }

    public function setMoneyTransfers(array $transfers)
    {
        $this->moneyTransfers = $transfers;

        return $this;
    }

    /**
     * @param array $accounts
     *
     * @return \AppBundle\Entity\Report
     */
    public function setAccounts($accounts)
    {
        foreach ($accounts as $account) {
            $account->setReport($this);
        }

        $this->accounts = $accounts;

        return $this;
    }

    /**
     * @return array $contacts
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * @param array $contacts
     *
     * @return array $contacts
     */
    public function setContacts($contacts)
    {
        $this->contacts = $contacts;

        return $this->contacts;
    }

    /**
     * @var array
     */
    public function getDecisions()
    {
        return $this->decisions;
    }

    /**
     * @param type $decisions
     *
     * @return \AppBundle\Entity\Report
     */
    public function setDecisions($decisions)
    {
        $this->decisions = $decisions;

        return $this;
    }

    /**
     * @param array $assets
     *
     * @return \AppBundle\Entity\Report
     */
    public function setAssets($assets)
    {
        $this->assets = $assets;

        return $this;
    }

    /**
     * @return array $assets
     */
    public function getAssets()
    {
        return $this->assets;
    }

    /**
     * @param ExecutionContextInterface $context
     */
    public function isValidEndDate(ExecutionContextInterface $context)
    {
        if ($this->startDate > $this->endDate) {
            $context->addViolationAt('endDate', 'report.endDate.beforeStart');
        }
    }

    /**
     * @param ExecutionContextInterface $context
     *
     * @return type
     */
    public function isValidDateRange(ExecutionContextInterface $context)
    {
        if (!empty($this->endDate) && !empty($this->startDate)) {
            $dateInterval = $this->startDate->diff($this->endDate);
        } else {
            $context->addViolationAt('endDate', 'report.endDate.invalidMessage');

            return;
        }

        if ($dateInterval->days > 366) {
            $context->addViolationAt('endDate', 'report.endDate.greaterThan12Months');
        }
    }

    /**
     * Return true when the report is Due (today's date => report end date).
     *
     * @return bool
     * @Assert\True(message="report.submissionExceptions.due", groups={"due"})
     */
    public function isDue()
    {
        if (!$this->getEndDate() instanceof \DateTime) {
            return false;
        }

        // reset time on dates
        $today = new \DateTime();
        $today->setTime(0, 0, 0);

        $reportDueOn = clone $this->getEndDate();
        $reportDueOn->setTime(0, 0, 0);

        return $today >= $reportDueOn;
    }

    /**
     * @param string $reasonForNoContacts
     *
     * @return \AppBundle\Entity\Report
     */
    public function setReasonForNoContacts($reasonForNoContacts)
    {
        $this->reasonForNoContacts = $reasonForNoContacts;

        return $this;
    }

    /**
     * @return string $reasonForNoContacts
     */
    public function getReasonForNoContacts()
    {
        return $this->reasonForNoContacts;
    }

    /**
     * @param string $reasonForNoDecisions
     *
     * @return \AppBundle\Entity\Report
     */
    public function setReasonForNoDecisions($reasonForNoDecisions)
    {
        $this->reasonForNoDecisions = $reasonForNoDecisions;

        return $this;
    }

    /**
     * @return string $reasonForNoDecisions
     */
    public function getReasonForNoDecisions()
    {
        return $this->reasonForNoDecisions;
    }

    /**
     * @return \AppBundle\Entity\Safeguarding
     */
    public function getSafeguarding()
    {
        return $this->safeguarding;
    }

    /**
     * @param \AppBundle\Entity\Safeguarding $safeguarding
     */
    public function setSafeguarding($safeguarding)
    {
        $this->safeguarding = $safeguarding;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function setAction(Action $action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @return MentalCapacity
     */
    public function getMentalCapacity()
    {
        return $this->mentalCapacity;
    }

    /**
     * @param MentalCapacity $mentalCapacity
     */
    public function setMentalCapacity(MentalCapacity $mentalCapacity)
    {
        $this->mentalCapacity = $mentalCapacity;

        return $this;
    }

    /**
     * @return bool $noAssetToAdd
     */
    public function getNoAssetToAdd()
    {
        return $this->noAssetToAdd;
    }

    /**
     * @param bool $noAssetToAdd
     *
     * @return \AppBundle\Entity\Report
     */
    public function setNoAssetToAdd($noAssetToAdd)
    {
        $this->noAssetToAdd = $noAssetToAdd;

        return $this;
    }

    /**
     * @return bool
     */
    public function getNoTransfersToAdd()
    {
        return $this->noTransfersToAdd;
    }

    /**
     * @param bool
     */
    public function setNoTransfersToAdd($noTransfersToAdd)
    {
        $this->noTransfersToAdd = $noTransfersToAdd;

        return $this;
    }

    /**
     * @return bool $submitted
     */
    public function getSubmitted()
    {
        return $this->submitted;
    }

    /**
     * @param type $submitted
     *
     * @return \AppBundle\Entity\Report
     */
    public function setSubmitted($submitted)
    {
        $this->submitted = $submitted;

        return $this;
    }

    /**
     * @return bool
     */
    public function getReviewed()
    {
        return $this->reviewed;
    }

    /**
     * @param bool $reviewed
     */
    public function setReviewed($reviewed)
    {
        $this->reviewed = $reviewed;
    }

    /**
     * @return string
     */
    public function getFurtherInformation()
    {
        return $this->furtherInformation;
    }

    /**
     * @param string $furtherInformation
     */
    public function setFurtherInformation($furtherInformation)
    {
        $this->furtherInformation = $furtherInformation;
    }

    /**
     * @param type $reportSeen
     *
     * @return \AppBundle\Entity\Report
     */
    public function setReportSeen($reportSeen)
    {
        $this->reportSeen = $reportSeen;
    }

    /**
     * @return type
     */
    public function getReportSeen()
    {
        return $this->reportSeen;
    }

    /**
     * @return bool
     */
    public function isAgree()
    {
        return $this->agree;
    }

    /**
     * @param bool $agree
     */
    public function setAgree($agree)
    {
        $this->agree = $agree;
    }

    /**
     * @return string
     */
    public function getAgreedBehalfDeputy()
    {
        return $this->agreedBehalfDeputy;
    }

    /**
     * @param string $agreedBehalfDeputy
     */
    public function setAgreedBehalfDeputy($agreedBehalfDeputy)
    {
        $this->agreedBehalfDeputy = $agreedBehalfDeputy;

        return $this;
    }

    /**
     * @return string
     */
    public function getAgreedBehalfDeputyExplanation()
    {
        return $this->agreedBehalfDeputyExplanation;
    }

    /**
     * @param string $agreedBehalfDeputyExplanation
     */
    public function setAgreedBehalfDeputyExplanation($agreedBehalfDeputyExplanation)
    {
        $this->agreedBehalfDeputyExplanation = $agreedBehalfDeputyExplanation;

        return $this;
    }

    /**
     * @return Transaction[]
     */
    public function getTransactionsIn()
    {
        return $this->transactionsIn;
    }

    /**
     * @param Transaction[] $transactionsIn
     */
    public function setTransactionsIn($transactionsIn)
    {
        $this->transactionsIn = $transactionsIn;
    }

    /**
     * @return Transaction[]
     */
    public function getTransactionsOut()
    {
        return $this->transactionsOut;
    }

    /**
     * @param Transaction[] $transactionsOut
     */
    public function setTransactionsOut($transactionsOut)
    {
        $this->transactionsOut = $transactionsOut;
    }



    /**
     * @param Transaction[] $transactions
     *
     * @return array array of [category=>[entries=>[[id=>,type=>]], amountTotal[]]]
     */
    public function groupByCategory(array $transactions)
    {
        $ret = [];

        foreach ($transactions as $id => $transaction) {
            $cat = $transaction->getCategory();
            if (!isset($ret[$cat])) {
                $ret[$cat] = ['entries' => [], 'amountTotal' => 0];
            }
            $ret[$cat]['entries'][$id] = $transaction; // needed to find the corresponding transaction in the form
            $ret[$cat]['amountTotal'] += $transaction->getAmountsTotal();
        }

        return $ret;
    }

    /**
     * @return float
     */
    public function getMoneyInTotal()
    {
        return $this->moneyInTotal;
    }

    /**
     * @param float $moneyInTotal
     */
    public function setMoneyInTotal($moneyInTotal)
    {
        $this->moneyInTotal = $moneyInTotal;
    }

    /**
     * @return float
     */
    public function getMoneyOutTotal()
    {
        return $this->moneyOutTotal;
    }

    /**
     * @param float $moneyOutTotal
     */
    public function setMoneyOutTotal($moneyOutTotal)
    {
        $this->moneyOutTotal = $moneyOutTotal;
    }

    /**
     * @return float
     */
    public function getAccountsOpeningBalanceTotal()
    {
        return $this->accountsOpeningBalanceTotal;
    }

    /**
     * @param float $accountsOpeningBalanceTotal
     */
    public function setAccountsOpeningBalanceTotal($accountsOpeningBalanceTotal)
    {
        $this->accountsOpeningBalanceTotal = $accountsOpeningBalanceTotal;
    }

    /**
     * @return float
     */
    public function getAccountsClosingBalanceTotal()
    {
        return $this->accountsClosingBalanceTotal;
    }

    /**
     * @param float $accountsClosingBalanceTotal
     */
    public function setAccountsClosingBalanceTotal($accountsClosingBalanceTotal)
    {
        $this->accountsClosingBalanceTotal = $accountsClosingBalanceTotal;
    }

    /**
     * @return float
     */
    public function getCalculatedBalance()
    {
        return $this->calculatedBalance;
    }

    /**
     * @param float $calculatedBalance
     */
    public function setCalculatedBalance($calculatedBalance)
    {
        $this->calculatedBalance = $calculatedBalance;
    }

    /**
     * @return float
     */
    public function getTotalsOffset()
    {
        return $this->totalsOffset;
    }

    /**
     * @param float $totalsOffset
     */
    public function setTotalsOffset($totalsOffset)
    {
        $this->totalsOffset = $totalsOffset;
    }

    /**
     * @return bool
     */
    public function isTotalsMatch()
    {
        return $this->totalsMatch;
    }

    /**
     * @param bool $totalsMatch
     */
    public function setTotalsMatch($totalsMatch)
    {
        $this->totalsMatch = $totalsMatch;
    }

    /**
     * @return string
     */
    public function getBalanceMismatchExplanation()
    {
        return $this->balanceMismatchExplanation;
    }

    /**
     * @param string $balanceMismatchExplanation
     */
    public function setBalanceMismatchExplanation($balanceMismatchExplanation)
    {
        $this->balanceMismatchExplanation = $balanceMismatchExplanation;
    }

    /**
     ** @return bool
     */
    public function hasAccounts()
    {
        return count($this->getAccounts()) > 0;
    }

    /**
     ** @return bool
     */
    public function hasMoneyIn()
    {
        return count(array_filter($this->getTransactionsIn() ?: [], function ($t) {
            return count(array_filter($t->getAmounts())) > 0;
        })) > 0;
    }

    /**
     ** @return bool
     */
    public function hasMoneyOut()
    {
        return count(array_filter($this->getTransactionsOut() ?: [], function ($t) {
             return count(array_filter($t->getAmounts())) > 0;
        })) > 0;
    }

    /**
     ** @return Account[]
     */
    public function getAccountsWithNoClosingBalance()
    {
        return array_filter($this->getAccounts(), function ($account) {
            /* @var $account Account */
            return $account->getClosingBalance() === null;
        });
    }

    /**
     ** @return bool
     */
    public function isMissingMoneyOrAccountsOrClosingBalance()
    {
        return !$this->hasMoneyIn()
            || !$this->hasMoneyOut()
            || !$this->hasAccounts()
            || count($this->getAccountsWithNoClosingBalance()) > 0;
    }

    /**
     * @param int $id
     * 
     * @return bool
     */
    public function hasAssetWithId($id)
    {
        foreach ($this->getAssets() as $asset) {
            if ($asset->getId() == $id) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param int $id
     * 
     * @return bool
     */
    public function hasAaccountWithId($id)
    {
        foreach ($this->getAccounts() as $asset) {
            if ($asset->getId() == $id) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Debt[]
     */
    public function getDebts()
    {
        return $this->debts;
    }

    /**
     * @param $value string yes/no
     */
    public function setHasDebts($value)
    {
        if ($value == 'no') {
            foreach ($this->debts as $debt) {
                $debt->setAmount(null);
                $debt->setMoreDetails(null);
            }
        }
    }

    /**
     * @return string yes/no
     */
    public function getHasDebts()
    {
        foreach($this->debts as $debt) {
            if ($debt->getAmount()) {
                return 'yes';
            }
        }

        return 'no';
    }

    /**
     * @param Debt[] $debts
     */
    public function setDebts($debts)
    {
        $this->debts = $debts;
    }

    /**
     * @return decimal
     */
    public function getDebtsTotalAmount()
    {
        return $this->debtsTotalAmount;
    }

    /**
     * @param decimal $debtsTotalAmount
     */
    public function setDebtsTotalAmount($debtsTotalAmount)
    {
        $this->debtsTotalAmount = $debtsTotalAmount;
    }
       

}
