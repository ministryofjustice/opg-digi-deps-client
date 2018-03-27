<?php

namespace AppBundle\Entity\Report;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class MoneyTransaction
{
    /**
     * Keep in sync with API
     * No need to do a separate call to get the list
     * Possible refactor would be moving some entities data into a shared library
     *
     * @JMS\Exclude
     */
    public static $categories = [
        // group => categories[] => category => config['hasMoreDetails', 'type']
        // Money In
        'salary-or-wages' => [
            'categories' => [],
            'config' => ['hasDetails' => false, 'type' => 'in']
        ],
        'income-and-earnings' => [
            'categories' => [
                'account-interest' => ['config' => ['hasDetails' => false, 'type' => 'in']],
                'dividends' => ['config' => ['hasDetails' => false, 'type' => 'in']],
                'income-from-property-rental' => ['config' => ['hasDetails' => false, 'type' => 'in']],
            ],
            'config' => ['hasDetails' => false, 'type' => 'in']
        ],
        'pensions' => [
            'categories' => [
                'personal-pension' => ['config' => ['hasDetails' => false, 'type' => 'in']],
                'state-pension' => ['config' => ['hasDetails' => false, 'type' => 'in']],
            ],
            'config' => ['hasDetails' => false, 'type' => 'in']
        ],
        'state-benefits' => [
            'categories' => [
                'attendance-allowance' => ['config' => ['hasDetails' => false, 'type' => 'in']],
                'disability-living-allowance' => ['config' => ['hasDetails' => false, 'type' => 'in']],
                'employment-support-allowance' => ['config' => ['hasDetails' => false, 'type' => 'in']],
                'housing-benefit' => ['config' => ['hasDetails' => false, 'type' => 'in']],
                'incapacity-benefit' => ['config' => ['hasDetails' => false, 'type' => 'in']],
                'income-support' => ['config' => ['hasDetails' => false, 'type' => 'in']],
                'pension-credit' => ['config' => ['hasDetails' => false, 'type' => 'in']],
                'personal-independence-payment' => ['config' => ['hasDetails' => false, 'type' => 'in']],
                'severe-disablement-allowance' => ['config' => ['hasDetails' => false, 'type' => 'in']],
                'universal-credit' => ['config' => ['hasDetails' => false, 'type' => 'in']],
                'winter-fuel-cold-weather-payment' => ['config' => ['hasDetails' => false, 'type' => 'in']],
                'other-benefits' => ['config' => ['hasDetails' => true, 'type' => 'in']],
            ],
            'config' => ['hasDetails' => false, 'type' => 'in']
        ],
        'compensation-or-damages-award' => [
            'categories' => [],
            'config' => ['hasDetails' => true, 'type' => 'in']
        ],
        'one-off' => [
            'categories' => [
                'bequest-or-inheritance' => ['config' => ['hasDetails' => false, 'type' => 'in']],
                'cash-gift-received' => ['config' => ['hasDetails' => false, 'type' => 'in']],
                'refunds' => ['config' => ['hasDetails' => false, 'type' => 'in']],
                'sale-of-asset' => ['config' => ['hasDetails' => true, 'type' => 'in']],
                'sale-of-investment' => ['config' => ['hasDetails' => true, 'type' => 'in']],
                'sale-of-property' => ['config' => ['hasDetails' => true, 'type' => 'in']],
            ],
            'config' => ['hasDetails' => false, 'type' => 'in']
        ],
        'moneyin-other' => [
            'categories' => [],
            'config' => ['hasDetails' => true, 'type' => 'in']
        ],

        // Money Out
        'household-bills' => [
            'categories' => [
                'broadband' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'council-tax' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'electricity' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'food' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'gas' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'insurance-eg-life-home-contents' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'other-insurance' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'property-maintenance-improvement' => ['config' => ['hasDetails' => true, 'type' => 'out']],
                'telephone' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'tv-services'=> ['config' => ['hasDetails' => false, 'type' => 'out']],
                'water' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'households-bills-other' => ['config' => ['hasDetails' => true, 'type' => 'out']],
            ],
            'config' => ['hasDetails' => false, 'type' => 'out']
        ],
        'accommodation' => [
            'categories' => [
                'accommodation-service-charge' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'mortgage' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'rent' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'accommodation-other' => ['config' => ['hasDetails' => true, 'type' => 'out']],
            ],
            'config' => ['hasDetails' => false, 'type' => 'out']
        ],
        'care-and-medical' => [
            'categories' => [
                'care-fees' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'local-authority-charges-for-care' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'medical-expenses' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'medical-insurance' => ['config' => ['hasDetails' => false, 'type' => 'out']],
            ],
            'config' => ['hasDetails' => false, 'type' => 'out']
        ],
        'client-expenses' => [
            'categories' => [
                'client-transport-bus-train-taxi-fares' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'clothes' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'day-trips' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'holidays' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'personal-allowance-pocket-money' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'toiletries' => ['config' => ['hasDetails' => false, 'type' => 'out']],
            ],
            'config' => ['hasDetails' => false, 'type' => 'out']
        ],
        'fees' => [
            'categories' => [
                'deputy-security-bond' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'opg-fees' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'professional-fees-eg-solicitor-accountant' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'other-fees' => ['config' => ['hasDetails' => true, 'type' => 'out']]
            ],
            'config' => ['hasDetails' => false, 'type' => 'out']
        ],
        'major-purchases' => [
            'categories' => [
                'investment-bonds-purchased' => ['config' => ['hasDetails' => true, 'type' => 'out']],
                'investment-account-purchased' => ['config' => ['hasDetails' => true, 'type' => 'out']],
                'stocks-and-shares-purchased' => ['config' => ['hasDetails' => true, 'type' => 'out']],
                'purchase-over-1000' => ['config' => ['hasDetails' => true, 'type' => 'out']]
            ],
            'config' => ['hasDetails' => false, 'type' => 'out']
        ],
        'debt-and-charges' => [
            'categories' => [
                'bank-charges' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'credit-cards-charges' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'loans' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'tax-payments-to-hmrc' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'unpaid-care-fees' => ['config' => ['hasDetails' => false, 'type' => 'out']],
                'debt-and-charges-other' => ['config' => ['hasDetails' => true, 'type' => 'out']],
            ],
            'config' => ['hasDetails' => false, 'type' => 'out']
        ],
        'cash-withdrawn' => [
            'categories' => [],
            'config' => ['hasDetails' => true, 'type' => 'out']
        ],
        'transfers-out-to-other-accounts' => [
            'categories' => [],
            'config' => ['hasDetails' => true, 'type' => 'out']
        ],
        'moneyout-other' => [
            'categories' => [],
            'config' => ['hasDetails' => true, 'type' => 'out']
        ]
    ];


    /**
     * @JMS\Type("string")
     * @JMS\Groups({"transaction"})
     */
    private $id;

    /**
     * @JMS\Type("string")
     * @Assert\NotBlank(message="moneyTransaction.form.category.notBlank", groups={"transaction-group"})
     */
    private $group;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"transaction"})
     * @Assert\NotBlank(message="moneyTransaction.form.category.notBlank", groups={"transaction-category"})
     */
    private $category;

    /**
     * @JMS\Type("string")
     */
    private $type;

    /**
     * @var array
     *
     * @JMS\Type("string")
     * @JMS\Groups({"transaction"})
     *
     * @Assert\NotBlank(message="moneyTransaction.form.amount.notBlank", groups={"transaction-amount"})
     * @Assert\Range(min=0.01, max=10000000, minMessage = "moneyTransaction.form.amount.minMessage", maxMessage = "moneyTransaction.form.amount.maxMessage", groups={"transaction-amount"})
     */
    private $amount;

    /**
     * @var string
     * @JMS\Groups({"transaction"})
     *
     * @Assert\NotBlank(message="moneyTransaction.form.description.notBlank", groups={"transaction-description"})
     *
     * @JMS\Type("string")
     */
    private $description;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param mixed $group
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return array
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param array $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }
}
