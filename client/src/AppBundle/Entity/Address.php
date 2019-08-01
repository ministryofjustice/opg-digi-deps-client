<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Organisation
 */
class Address
{

    /**
     * @JMS\Type("integer")
     * @JMS\Groups({"address", "address-id"})

     * @var int
     */
    private $id;

    /**
     * @var Organisation
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Organisation", inversedBy="addresses", cascade={"persist"})
     * @ORM\JoinColumn(name="organisation_id", referencedColumnName="id")
     */
    private $organisation;

    /**
     * @var string
     *
     * @JMS\Groups({"address"})
     * @JMS\Type("integer")
     */
    private $deputyAddressNo;

    /**
     * @var string
     *
     * @JMS\Groups({"address"})
     * @JMS\Type("string")
     */
    private $address1;

    /**
     * @var string
     *
     * @JMS\Groups({"address"})
     * @JMS\Type("string")
     */
    private $email1;

    /**
     * @var string
     *
     * @JMS\Groups({"address"})
     * @JMS\Type("string")
     */
    private $email2;

    /**
     * @var string
     *
     * @JMS\Groups({"address"})
     * @JMS\Type("string")
     */
    private $email3;

    /**
     * @var string
     *
     * @JMS\Groups({"address"})
     * @JMS\Type("string")
     */
    private $address2;

    /**
     * @var string
     *
     * @JMS\Groups({"address"})
     * @JMS\Type("string")
     */
    private $address3;

    /**
     * @var string
     *
     * @JMS\Groups({"address"})
     * @JMS\Type("string")
     */
    private $address4;

    /**
     * @var string
     *
     * @JMS\Groups({"address"})
     * @JMS\Type("string")
     */
    private $address5;

    /**
     * @var string
     *
     * @JMS\Groups({"address"})
     * @JMS\Type("string")
     */
    private $postcode;

    /**
     * @var string
     *
     * @JMS\Groups({"address"})
     * @JMS\Type("string")
     */
    private $country;

    /**
     * Address constructor.
     *
     * @param array $address
     */
    public function __construct(array $address)
    {
        $this->address1 = array_key_exists('address1', $address) ? $address['address1'] : null;
        $this->address2 = array_key_exists('address2', $address) ? $address['address2'] : null;
        $this->address3 = array_key_exists('address3', $address) ? $address['address3'] : null;
        $this->address4 = array_key_exists('address4', $address) ? $address['address4'] : null;
        $this->address5 = array_key_exists('address5', $address) ? $address['address5'] : null;
        $this->postcode = array_key_exists('postcode', $address) ? $address['postcode'] : null;
        $this->country = array_key_exists('country', $address) ? $address['country'] : null;
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
     * @return Address
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return Organisation
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * @param mixed $organisation
     *
     * @return Address
     */
    public function setOrganisation($organisation)
    {
        $this->organisation = $organisation;
        return $this;
    }

    /**
     * @return integer
     */
    public function getDeputyAddressNo()
    {
        return $this->deputyAddressNo;
    }

    /**
     * @param integer $deputyAddressNo
     *
     * @return Address
     */
    public function setDeputyAddressNo($deputyAddressNo)
    {
        $this->deputyAddressNo = $deputyAddressNo;
    }

    /**
     * @return string
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * @param string $address1
     *
     * @return Address
     */
    public function setAddress1($address1)
    {
        $this->address1 = $address1;
    }

    /**
     * @return string
     */
    public function getEmail1()
    {
        return $this->email1;
    }

    /**
     * @param string $email1
     *
     * @return Address
     */
    public function setEmail1($email1)
    {
        $this->email1 = $email1;
    }

    /**
     * @return string
     */
    public function getEmail2()
    {
        return $this->email2;
    }

    /**
     * @param string $email2
     *
     * @return Address
     */
    public function setEmail2($email2)
    {
        $this->email2 = $email2;
    }

    /**
     * @return string
     */
    public function getEmail3()
    {
        return $this->email3;
    }

    /**
     * @param string $email3
     *
     * @return Address
     */
    public function setEmail3($email3)
    {
        $this->email3 = $email3;
    }

    /**
     * @return string
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * @param string $address2
     *
     * @return Address
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;
    }

    /**
     * @return string
     */
    public function getAddress3()
    {
        return $this->address3;
    }

    /**
     * @param string $address3
     *
     * @return Address
     */
    public function setAddress3($address3)
    {
        $this->address3 = $address3;
    }

    /**
     * @return string
     */
    public function getAddress4()
    {
        return $this->address4;
    }

    /**
     * @param string $address4
     *
     * @return Address
     */
    public function setAddress4($address4)
    {
        $this->address4 = $address4;
    }

    /**
     * @return string
     */
    public function getAddress5()
    {
        return $this->address5;
    }

    /**
     * @param string $address5
     *
     * @return Address
     */
    public function setAddress5($address5)
    {
        $this->address5 = $address5;
    }

    /**
     * @return string
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * @param string $postcode
     *
     * @return Address
     */
    public function setPostcode($postcode)
    {
        $this->postcode = $postcode;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     *
     * @return Address
     */
    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }


}
