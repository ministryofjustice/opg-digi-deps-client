<?php

namespace AppBundle\Service\SectionValidator\Odr;
use AppBundle\Entity\Odr\Odr;

class ActionsValidator
{
    /**
     * @var Action
     */
    private $odr;

    /**
     * @param Odr $odr
     */
    public function __construct(Odr $odr)
    {
        $this->odr = $odr;
    }

    public function missing($question)
    {
        switch ($question) {
            case 'actionGiveGiftsToClient':
                return $this->odr->getActionGiveGiftsToClient() === null;
            case 'actionPropertyMaintenance':
                return $this->odr->getActionPropertyMaintenance() === null;
            case 'actionPropertySellingRent':
                return $this->odr->getActionPropertySellingRent() === null;
            case 'actionPropertyBuy':
                return $this->odr->getActionPropertyBuy() === null;
        }
    }

    /**
     * @return int
     */
    public function countMissing()
    {
        return count(array_filter([
            $this->odr->getActionGiveGiftsToClient() === null,
            $this->odr->getActionPropertyMaintenance() === null,
            $this->odr->getActionPropertySellingRent() === null,
            $this->odr->getActionPropertyBuy() === null,
        ]));
    }

}