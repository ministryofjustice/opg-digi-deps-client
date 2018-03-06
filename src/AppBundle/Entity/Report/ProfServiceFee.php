<?php

namespace AppBundle\Entity\Report;

use AppBundle\Entity\Report\Traits\HasReportTrait;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class ProfServiceFee
{
    const TYPE_ASSESSED_FEE = 'assessed';
    const TYPE_FIXED_FEE = 'fixed';

    const TYPE_PREVIOUS_FEE = 'previous';
    const TYPE_CURRENT_FEE = 'current';
    const TYPE_ESTIMATED_FEE = 'estimated';

    use HasReportTrait;

    /**
     * @JMS\Type("integer")
     * @JMS\Groups({"prof-service-fees"})
     *
     * @var int
     */
    private $id;

    /**
     * Hold service type
     *
     * If the order or any key is added, update the ReportControllerTest, hardcoded on position and number
     *  in order to keep it simple
     *
     * @var array
     */
    public static $serviceTypeIds = [
        // id => hasMoreDetails
        'annual-report' => false,
        'annual-management-interim' => false,
        'annual-management-final' => false,
        'appointment' => false,
        'conveyancing' => false,
        'litigation' => true,
        'specialist-advice' => true,
        'statutory-wills' => false,
        'tax-returns' => false,
        'trust-applications' => false,
        //'other-costs' => false
    ];

    /**
     * @JMS\Type("string")
     * @var string fixed|assessed
     *
     * @JMS\Groups({"prof-service-fees"})
     *
     * @Assert\NotBlank(message="profServiceFee.assessedOrFixed.notBlank", groups={"prof-service-fee-details-type"})
     */
    private $assessedOrFixed;

    /**
     * @JMS\Type("string")
     * @var string a value in self:$feeTypeIds
     *
     * @JMS\Groups({"prof-service-fees"})
     */
    private $feeTypeId;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"prof-service-fees"})
     *
     * @Assert\NotBlank(message="fee.otherFeeDetails.notBlank", groups={"other-prof-service-fees"})
     */
    private $otherFeeDetails;

    /**
     * @JMS\Type("string")
     * @var string a value in self:$serviceTypeIds
     *
     * @Assert\NotBlank(message="profServiceFee.serviceType.notBlank", groups={"prof-service-fee-type"})
     * @JMS\Groups({"prof-service-fees"})
     */
    private $serviceTypeId;

    /**
     * @var float
     *
     * @JMS\Type("string")
     * @JMS\Groups({"prof-service-fees"})
     * @Assert\NotBlank(message="profServiceFee.amountCharged.notBlank", groups={"prof-service-fee-details-type"})
     * @Assert\Range(min=0, max=100000000, minMessage = "fee.amount.minMessage", maxMessage = "fee.amount.maxMessage", groups={"prof-service-fee-details-type"})
     */
    private $amountCharged;

    /**
     * @var string yes|no
     *
     * @JMS\Type("string")
     * @JMS\Groups({"prof-service-fees"})
     * @Assert\NotBlank(message="profServiceFee.paymentReceived.notBlank", groups={"prof-service-fee-details-type"})
     */
    private $paymentReceived;

    /**
     * @var float
     *
     * @JMS\Type("string")
     * @JMS\Groups({"prof-service-fees"})
     * @Assert\NotBlank(message="profServiceFee.amountReceived.notBlank", groups={"prof-service-fee-details-type-payment-received"})
     * @Assert\Range(min=0, max=100000000, minMessage = "fee.amount.minMessage", maxMessage = "fee.amount.maxMessage", groups={"prof-service-fee-details-type-payment-received"})
     */
    private $amountReceived;

    /**
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @JMS\Groups({"prof-service-fees"})
     *
     * @Assert\Date(message="profServiceFee.paymentReceivedDate.invalidMessage", groups={"prof-service-fee-details-type-payment-received"})
     * @Assert\NotBlank(message="profServiceFee.paymentReceivedDate.notBlank", groups={"prof-service-fee-details-type-payment-received"})
     *
     * @var \DateTime
     */
    private $paymentReceivedDate;

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
    }

    /**
     * @return string
     */
    public function getAssessedOrFixed()
    {
        return $this->assessedOrFixed;
    }

    /**
     * @param string $assessedOrFixed
     */
    public function setAssessedOrFixed($assessedOrFixed)
    {
        $this->assessedOrFixed = $assessedOrFixed;
    }

    /**
     * @return mixed
     */
    public function getFeeTypeId()
    {
        return $this->feeTypeId;
    }

    /**
     * @param mixed $feeTypeId
     */
    public function setFeeTypeId($feeTypeId)
    {
        $this->feeTypeId = $feeTypeId;
    }

    /**
     * @return mixed
     */
    public function getOtherFeeDetails()
    {
        return $this->otherFeeDetails;
    }

    /**
     * @param mixed $otherFeeDetails
     */
    public function setOtherFeeDetails($otherFeeDetails)
    {
        $this->otherFeeDetails = $otherFeeDetails;
    }

    /**
     * @return float
     */
    public function getAmountCharged()
    {
        return $this->amountCharged;
    }

    /**
     * @param float $amountCharged
     */
    public function setAmountCharged($amountCharged)
    {
        $this->amountCharged = $amountCharged;
    }

    /**
     * @return float
     */
    public function getAmountReceived()
    {
        return $this->amountReceived;
    }

    /**
     * @param float $amountReceived
     */
    public function setAmountReceived($amountReceived)
    {
        $this->amountReceived = $amountReceived;
    }

    /**
     * @return mixed
     */
    public function getPaymentReceivedDate()
    {
        return $this->paymentReceivedDate;
    }

    /**
     * @param mixed $paymentReceivedDate
     */
    public function setPaymentReceivedDate($paymentReceivedDate)
    {
        $this->paymentReceivedDate = $paymentReceivedDate;
    }

    /**
     * @return string
     */
    public function getPaymentReceived()
    {
        return $this->paymentReceived;
    }

    /**
     * @param string $paymentReceived
     */
    public function setPaymentReceived($paymentReceived)
    {
        $this->paymentReceived = $paymentReceived;
    }

    /**
     * @return string
     */
    public function getServiceTypeId()
    {
        return $this->serviceTypeId;
    }

    /**
     * @param string $serviceTypeId
     */
    public function setServiceTypeId($serviceTypeId)
    {
        $this->serviceTypeId = $serviceTypeId;
    }

    /**
     * Is a current Fee?
     *
     * @return bool
     */
    public function isCurrentFee()
    {
        return $this->getFeeTypeId() == self::TYPE_CURRENT_FEE;
    }

    /**
     * Is a previous Fee?
     *
     * @return bool
     */
    public function isPreviousFee()
    {
        return $this->getFeeTypeId() == self::TYPE_PREVIOUS_FEE;
    }

    /**
     * Is a estimated Fee?
     *
     * @return bool
     */
    public function isEstimatedFee()
    {
        return $this->getFeeTypeId() == self::TYPE_ESTIMATED_FEE;
    }
}
