<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class DateBefore extends Constraint
{
    /**
     * @var
     */
    public $target;

    /**
     * @var
     */
    public $field;

    /**
     * @var string error message
     */
    public $message = 'Date invalid';

    /**
     * @var string exception message
     */
    public $exceptionMessage = 'Date could not be validated';

    /**
     * @var array validation groups
     */
    public $groups = [];

    /**
     * DValidDate constructor.
     *
     * @param mixed|null $options
     */
    public function __construct($options)
    {
        parent::__construct($options);

        if (isset($options['target']))
        {
            $this->target = $options['target'];
        }

        $this->target = $this->hasOption('target') ?
            new \DateTime($options['target']) :
            self::getDefaultTarget();
    }

    /**
     * @return \DateTime
     */
    public function getDefaultTarget()
    {
        return new \DateTime('+10 years');
    }

    /**
     * @return array
     */
    public function getRequiredOptions()
    {
        return ['field', 'message', 'groups'];
    }

    /**
     * @return mixed|string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return string
     */
    public function validatedBy()
    {
        return 'date_before';
    }

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }

    /**
     * Has this option been provided?
     *
     * @param $optionName
     * @return bool
     */
    public function hasOption($optionName)
    {
        return !empty(isset($this->$optionName));
    }

    /**
     * @return \DateTime
     */
    public function getTarget()
    {
        return $this->target;
    }
}
