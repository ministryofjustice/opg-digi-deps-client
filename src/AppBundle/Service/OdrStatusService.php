<?php

namespace AppBundle\Service;

use AppBundle\Entity\Odr\Odr;

class OdrStatusService
{
    const STATE_NOT_STARTED = 'not-started';
    const STATE_INCOMPLETE = 'incomplete';
    const STATE_DONE = 'done';

    /** @var Odr */
    private $odr;

    public function __construct(Odr $odr)
    {
        $this->odr = $odr;
    }

    /**
     * @return array
     */
    public function getRemainingSections()
    {
        $states = [
            'visitsCare' => $this->getVisitsCareState(),
            'finance' => $this->getFinanceState(),
            'assetsDebts' => $this->getAssetsDebtsState(),
            'actions' => $this->getActionsState(),
        ];

        return array_filter($states, function ($e) {
            return $e != self::STATE_DONE;
        });
    }

    /** @return bool */
    public function isReadyToSubmit()
    {
        return count($this->getRemainingSections()) === 0;
    }

    /**
     * @return string $status | null
     */
    public function getStatus()
    {
        if ($this->isReadyToSubmit()) {
            return 'readyToSubmit';
        }

        if ($this->getVisitsCareState() == self::STATE_NOT_STARTED
            && $this->getFinanceState() == self::STATE_NOT_STARTED
            && $this->getAssetsDebtsState() == self::STATE_NOT_STARTED
        ) {
            return 'notStarted';
        }

        return 'notFinished';
    }

    /** @return string */
    public function getVisitsCareState()
    {
        if (!$this->odr->getVisitsCare() || $this->odr->getVisitsCare()->missingInfo()) {
            return self::STATE_NOT_STARTED;
        } else {
            return self::STATE_DONE;
        }
    }

    public function getFinanceState()
    {
        if (empty($this->odr->getBankAccounts()) && $this->odr->incomeBenefitsStatus() == 'not-started') {
            return self::STATE_NOT_STARTED;
        }

        if (count($this->odr->getBankAccounts()) > 0 && $this->odr->incomeBenefitsStatus() == 'done') {
            return self::STATE_DONE;
        }

        return self::STATE_INCOMPLETE;
    }

    public function getAssetsDebtsState()
    {
        $hasAtLeastOneAsset = count($this->odr->getAssets()) > 0;
        $noAssetsToAdd = $this->odr->getNoAssetToAdd();
        $hasDebts = $this->odr->getHasDebts();

        if (!$hasAtLeastOneAsset && !$noAssetsToAdd && empty($hasDebts)) {
            return self::STATE_NOT_STARTED;
        }

        $assetsSubSectionComplete = $hasAtLeastOneAsset || $noAssetsToAdd;
        $debtsSectionComplete = in_array($hasDebts, ['yes', 'no']);

        if ($assetsSubSectionComplete && $debtsSectionComplete) {
            return self::STATE_DONE;
        }

        return self::STATE_INCOMPLETE;
    }

    public function getActionsState()
    {
        $giftsStarted = !empty($this->odr->getActionGiveGiftsToClient());
        $propertyStarted = !empty($this->odr->getActionPropertyBuy())
            || !empty($this->odr->getActionPropertyMaintenance())
            || !empty($this->odr->getActionPropertySellingRent());
        $moreInfoStarted = !empty($this->odr->getActionMoreInfo());

        if (!$giftsStarted && !$propertyStarted && !$moreInfoStarted) {
            return self::STATE_NOT_STARTED;
        }

        if ($giftsStarted && $propertyStarted && $moreInfoStarted) {
            return self::STATE_DONE;
        }

        return self::STATE_INCOMPLETE;
    }
}
