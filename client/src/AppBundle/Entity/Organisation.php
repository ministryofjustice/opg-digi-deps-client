<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Traits\AddressTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Organisation
 */
class Organisation
{
    /**
     * @var int
     * @JMS\Type("integer")
     * @JMS\Groups({"organisation", "organisation-id"})
     */
    private $id;

    /**
     * @var string
     *
     * @JMS\Groups({"organisation"})
     * @JMS\Type("string")
     */
    private $organisationName;

    /**
     * @var string
     *
     * @JMS\Groups({"organisation"})
     * @JMS\Type("string")
     */
    private $emailDomain;

    /**
     * @var ArrayCollection
     * @JMS\Groups({"organisation-users"})
     *
     * @JMS\Type("ArrayCollection<AppBundle\Entity\User>")
     */
    private $deputies;

    /**
     * @var ArrayCollection
     * @JMS\Groups({"organisation-address"})
     *
     * @JMS\Type("ArrayCollection<AppBundle\Entity\Address>")
     */
    private $addresses;

    /**
     * Organisation constructor.
     *
     * @param $organisationName
     * @param Address $address
     */
    public function __construct($organisationName, Address $address)
    {
        $this->setOrganisationName($organisationName);
        $this->addAddress($address);
    }

    /**
     * @return string
     */
    public function getOrganisationName()
    {
        return $this->organisationName;
    }

    /**
     * @param $organisationName
     *
     * @return $this
     */
    public function setOrganisationName($organisationName)
    {
        $this->organisationName = $organisationName;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmailDomain()
    {
        return $this->emailDomain;
    }

    /**
     * @param string $emailDomain
     *
     * @return $this
     */
    public function setEmailDomain($emailDomain)
    {
        $this->emailDomain = $emailDomain;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getDeputies()
    {
        return $this->deputies;
    }

    /**
     * @param ArrayCollection $deputies
     *
     * @return $this
     */
    public function setDeputies($deputies)
    {
        $this->deputies = $deputies;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * @param ArrayCollection $addresses
     *
     * @return $this
     */
    public function setAddresses(ArrayCollection $addresses)
    {
        $this->addresses = $addresses;
        return $this;
    }

    /**
     * Add an address tto the organisation
     *
     * @param Address $address
     *
     * @return Organisation
     */
    public function addAddress(Address $address) {
        if (!$this->getAddresses()->contains($address)) {
            $this->addresses->add($address);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Organisation
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param User $deputy
     *
     * @return Organisation
     */
    public function addDeputy(User $deputy)
    {
        if (!$this->deputies->contains($deputy)) {
            $this->deputies->add($deputy);
        }

        return $this;
    }

    /**
     * Add Deputies
     *
     * @param ArrayCollection $deputies Deputies being added
     *
     * @return Organisation
     */
    public function addDeputies(ArrayCollection $deputies)
    {
        $this->deputies = new ArrayCollection(
            array_merge(
                $this->deputies->toArray(),
                $deputies->toArray()
            )
        );

        return $this;
    }

    /**
     * Remove a deputy from the collection
     *
     * @param mixed $deputy collection being removed
     *
     * @return Organisation
     */
    public function removeDeputy(User $deputy)
    {
        if ($this->deputies->contains($deputy)) {
            $this->deputies->removeElement($deputy);
        }

        return $this;
    }
}
