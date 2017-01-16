<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\ExclusionPolicy("none")
 */
class CourtOrderType
{
    /**
     * @JMS\Type("integer")
     * @var
     */
    private $id;
    /**
     * @var
     * @JMS\Type("string")
     */
    private $name;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return CourtOrderType
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return CourtOrderType
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

}