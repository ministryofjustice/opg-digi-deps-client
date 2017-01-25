<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\Client;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * @JMS\XmlRoot("report")
 * @JMS\ExclusionPolicy("none")
 * @Assert\Callback(methods={"isValidEndDate", "isValidDateRange"})
 * @Assert\Callback(methods={"debtsValid"}, groups={"debts"})
 */
class Report
{
    const TYPE_102 = '102';
    const TYPE_103 = '103';
    const TYPE_104 = '104';

    /**
     * 104 report
     */
    const PERSONAL_WELFARE = 1;

    /**
     * 102 report
     */
    const PROPERTY_AND_AFFAIRS = 2;

    /**
     * @JMS\Type("integer")
     * @JMS\Groups({"visits-care"})
     *
     * @var int
     */
    private $id;

    /**
     * @JMS\Type("string")
     *
     * see TYPE_* constant
     *
     * @var string
     */
    private $type;

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
     * @JMS\Type("array<AppBundle\Entity\Report\Account>")
     *
     * @var Account[]
     */
    private $bankAccounts;

    /**
     * @JMS\Type("array<AppBundle\Entity\Report\MoneyTransfer>")
     *
     * @var MoneyTransfer[]
     */
    private $moneyTransfers;

    /**
     * @JMS\Type("array<AppBundle\Entity\Report\Contact>")
     *
     * @var Contact[]
     */
    private $contacts;

    /**
     * @JMS\Type("array<AppBundle\Entity\Report\Asset>")
     *
     * @var Asset[]
     */
    private $assets;

    /**
     * @JMS\Type("double")
     *
     * @var float
     */
    private $assetsTotalValue;

    /**
     * @JMS\Type("array<AppBundle\Entity\Report\Decision>")
     *
     * @var Decision[]
     */
    private $decisions;

    /**
     * @JMS\Type("AppBundle\Entity\Report\VisitsCare")
     *
     * @var VisitsCare
     */
    private $visitsCare;

    /**
     * @JMS\Type("AppBundle\Entity\Report\Action")
     *
     * @var Action
     */
    private $action;

    /**
     * @JMS\Type("AppBundle\Entity\Report\MentalCapacity")
     *
     * @var MentalCapacity
     */
    private $mentalCapacity;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"reasonForNoContacts"})
     *
     * @Assert\NotBlank( message="contact.reasonForNoContacts.notBlank", groups={"reasonForNoContacts"})
     *
     * @var string
     */
    private $reasonForNoContacts;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"reasonForNoDecisions"})
     *
     * @Assert\NotBlank( message="decision.reasonForNoDecisions.notBlank", groups={"reason-no-decisions"})
     *
     * @var string
     */
    private $reasonForNoDecisions;

    /**
     * @JMS\Type("boolean")
     * @JMS\Groups({"noAssetsToAdd"})
     *
     * @var bool
     */
    private $noAssetToAdd;

    /**
     * @JMS\Type("boolean")
     * @JMS\Groups({"money-transfers-no-transfers"})
     *
     * @var bool
     */
    private $noTransfersToAdd;

    /**
     * @JMS\Type("boolean")
     * @JMS\Groups({"submit"})
     *
     * @var bool
     */
    private $submitted;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"furtherInformation"})
     *
     * @var string
     */
    private $furtherInformation;

    /** @var bool
     * @JMS\Type("boolean")
     * @Assert\True(message="report.agree", groups={"declare"} )
     */
    private $agree;

    /** 
     * @var string
     *              
     * @JMS\Type("string")
     * @JMS\Groups({"report","submit"})
     * @Assert\NotBlank(message="report.agreedBehalfDeputy.notBlank", groups={"declare"} )
     */
    private $agreedBehalfDeputy;

    /** 
     * @var string
     *              
     * @JMS\Type("string")
     * @JMS\Groups({"report","submit"})
     * @Assert\NotBlank(message="report.agreedBehalfDeputyExplanation.notBlank", groups={"declare-explanation"} )
     */
    private $agreedBehalfDeputyExplanation;

    /**
     * @JMS\Type("array<AppBundle\Entity\Report\Transaction>")
     * @JMS\Groups({"transactionsIn"})
     *
     * @var Transaction[]
     */
    private $transactionsIn;

    /**
     * @JMS\Type("array<AppBundle\Entity\Report\Transaction>")
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
     * @Assert\NotBlank(message="report.balanceMismatchExplanation.notBlank", groups={"balance"})
     * @Assert\Length( min=10, minMessage="report.balanceMismatchExplanation.length", groups={"balance"})
     *
     * @var string
     */
    private $balanceMismatchExplanation;

    /**
     * @JMS\Type("array<AppBundle\Entity\Report\Debt>")
     * @JMS\Groups({"debt"})
     *
     * @var ArrayCollection
     */
    private $debts;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"debt"})
     *
     * @Assert\NotBlank(message="report.hasDebts.notBlank", groups={"debts"})
     *
     * @var string
     */
    private $hasDebts;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"debt"})
     *
     * @var decimal
     */
    private $debtsTotalAmount;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"report-metadata"})
     *
     * @var decimal
     */
    private $metadata;

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
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
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
     * @return Client
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
    public function getBankAccounts()
    {
        return $this->bankAccounts;
    }

    /**
     * @return Account
     */
    public function getAccountWithId($id)
    {
        foreach($this->bankAccounts as $account) {
            if ($account->getId() == $id) {
                return $account;
            }
        }
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
     * @param array $bankAccounts
     *
     * @return \AppBundle\Entity\Report
     */
    public function setBankAccounts($bankAccounts)
    {
        foreach ($bankAccounts as $account) {
            $account->setReport($this);
        }

        $this->bankAccounts = $bankAccounts;

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

        return $this;
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
     * @return Asset[]
     */
    public function getAssets()
    {
        return $this->assets;
    }

    /**
     * Get assets total value.
     *
     * @return float
     */
    public function getAssetsTotalValue()
    {
        return $this->assetsTotalValue;
    }

    /**
     * Get debts total value.
     *
     * @return float
     */
    public function getDebtsTotalValue()
    {
        $ret = 0;
        foreach ($this->getDebts() as $debt) {
            $ret += $debt->getAmount();
        }

        return $ret;
    }


    /**
     * @param $debtId
     * @return Debt|null
     */
    public function getDebtById($debtId)
    {
        foreach ($this->getDebts() as $debt) {
            if ($debt->getDebtTypeId() == $debtId) {
                return $debt;
            }
        }

        return null;
    }

    /**
     * Used in the list view
     * AssetProperty is considered having title "Property"
     * Artwork, Antiques, Jewellery are grouped into "Artwork, antiques and jewellery".
     *
     * @return array $assets e.g. [Property => [asset1, asset2], Bonds=>[]...]
     */
    public function getAssetsGroupedByTitle()
    {
        // those needs to be grouped together
        $titleToGroupOverride = [
            'Artwork' => 'Artwork, antiques and jewellery',
            'Antiques' => 'Artwork, antiques and jewellery',
            'Jewellery' => 'Artwork, antiques and jewellery',
        ];

        $ret = [];
        foreach ($this->assets as $asset) {
            // select title
            if ($asset instanceof AssetProperty) {
                $title = 'Property';
            } elseif ($asset instanceof AssetOther) {
                $title = isset($titleToGroupOverride[$asset->getTitle()]) ?
                    $titleToGroupOverride[$asset->getTitle()] : $asset->getTitle();
            }

            // add asset into "items" and sum total
            $ret[$title]['items'][$asset->getId()] = $asset;
            $ret[$title]['total'] = isset($ret[$title]['total'])
                ? $ret[$title]['total'] + $asset->getValueTotal()
                : $asset->getValueTotal();
        }

        // order categories
        ksort($ret);
        // foreach category, order assets by ID desc
        foreach ($ret as &$row) {
            krsort($row['items']);
        }

        return $ret;
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

    public function hasContacts()
    {
        if (empty($this->getContacts()) && $this->getReasonForNoContacts() === null){
            return null;
        }

        return $this->getReasonForNoContacts() ? 'no' : 'yes';
    }

    public function setHasContacts($value)
    {
        // necessary to simplify form logic
        return null;
    }

    public function hasDecisions()
    {
        if (empty($this->getDecisions()) && $this->getReasonForNoDecisions() === null){
            return null;
        }

        return $this->getReasonForNoDecisions() ? 'no' : 'yes';
    }

    public function setHasDecisions($value)
    {
        // necessary to simplify form logic
        return null;
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
     * @return \AppBundle\Entity\Report\VisitsCare
     */
    public function getVisitsCare()
    {
        return $this->visitsCare;
    }

    /**
     * @param \AppBundle\Entity\Report\VisitsCare $visitsCare
     */
    public function setVisitsCare($visitsCare)
    {
        $this->visitsCare = $visitsCare;
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
     * //TODO improve this
     * @return Transaction[]
     */
    public function getValidTransactions($transactions)
    {
        return array_filter($transactions, function($t){
            return $t->getAmounts()[0] > 0;
        });
    }

    /**
     * //TODO improve this
     * @return Transaction[]
     */
    public function getTransactionsInWithId($id)
    {
        return array_filter($transactions, function($t) use ($id) {
            return $t->getId() == $id;
        });
    }

    /**
     * @param Transaction[] $transactionsIn
     */
    public function setTransactionsIn($transactionsIn)
    {
        $this->transactionsIn = $transactionsIn;

        return $this;
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

        return $this;
    }

    public function getTransactionCategories(array $transactions)
    {
        $ret = [];
        foreach ($transactions as $id => $transaction) {
            $ret[$transaction->getCategory()] = 'form.category.entries.' . $transaction->getCategory();
        }
        $ret = array_unique($ret);

        return $ret;
    }

    public function getTransactionIds(array $transactions, $category)
    {
        $ret = [];
        foreach ($transactions as $id => $transaction) {
            if ($category == $transaction->getCategory()) {
                $ret[$transaction->getId()] = 'form.id.entries.' . $transaction->getId() . '.label';
            }
        }
        $ret = array_unique($ret);

        return $ret;
    }

    /**
     * @param Transaction[] $transactions
     *
     * @return array array of [category=>[entries=>[[id=>,type=>]], amountTotal[]]]
     */
    public function groupByGroup(array $transactions)
    {
        $ret = [];

        foreach ($transactions as $id => $transaction) {
            $group = $transaction->getGroup();
            if (!isset($ret[$group])) {
                $ret[$group] = ['entries' => [], 'amountTotal' => 0];
            }
            $ret[$group]['entries'][$id] = $transaction; // needed to find the corresponding transaction in the form
            $ret[$group]['amountTotal'] += $transaction->getAmount();
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

        return $this;
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

        return $this;
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

        return $this;
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

        return $this;
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

        return $this;
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
        return count($this->getBankAccounts()) > 0;
    }

    /**
     ** @return bool
     */
    public function hasMoneyIn()
    {
        return count($this->getTransactionsIn()) > 0;
    }

    /**
     ** @return bool
     */
    public function hasMoneyOut()
    {
        return count($this->getTransactionsOut()) > 0;
    }

    /**
     ** @return Account[]
     */
    public function getAccountsWithNoClosingBalance()
    {
        return array_filter($this->getBankAccounts(), function ($account) {
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
    public function hasAccountWithId($id)
    {
        foreach ($this->getBankAccounts() as $e) {
            if ($e->getId() == $id) {
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
     * @param Debt[] $debts
     */
    public function setDebts($debts)
    {
        $this->debts = $debts;

        return $this;
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

        return $this;
    }

    /**
     * @return string
     */
    public function getHasDebts()
    {
        return $this->hasDebts;
    }

    /**
     * @param string $hasDebts
     */
    public function setHasDebts($hasDebts)
    {
        $this->hasDebts = $hasDebts;

        return $this;
    }

    /**
     * @return Debt[]
     */
    public function getDebtsWithValidAmount()
    {
        $debtsWithAValidAmount = array_filter($this->debts, function($debt) {
            return !empty($debt->getAmount());
        });

        return $debtsWithAValidAmount;
    }

    /**
     * @param ExecutionContextInterface $context
     */
    public function debtsValid(ExecutionContextInterface $context)
    {
        if ($this->getHasDebts() == 'yes' && count($this->getDebtsWithValidAmount()) === 0) {
            $context->addViolation('report.hasDebts.mustHaveAtLeastOneDebt');
        }
    }

}
