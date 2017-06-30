<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class DValidDate extends Constraint
{
    const DEFAULT_MIN_DATE = '01-01-1900';

    /**
     * @var
     */
    public $fieldName;

    /**
     * @var string error message
     */
    public $message = 'INVALID DATE';

    /**
     * @var string
     */
    public $exceptionMessage = 'We were unable to validate the following date: {{ ';

    /**
     * @var string error message
     */
    public $minDateErrorMessage = 'Date provided is too early';

    /**
     * @var string error message
     */
    public $maxDateErrorMessage = 'Date provided is too late';

    /**
     * @var array validation groups
     */
    public $groups = [];

    /**
     * @var \DateTime
     */
    private $minDate;

    /**
     * @var \DateTime
     */
    private $maxDate;

    /**
     * DValidDate constructor.
     *
     * @param mixed|null $options
     */
    public function __construct($options)
    {
        $this->minDate = $this->hasOption('min_date') ?
            new \DateTime($options['min_date']) :
            new \DateTime(self::DEFAULT_MIN_DATE);

        $this->maxDate = $this->hasOption('max_date') ?
            new \DateTime($options['max_date']) :
            new \DateTime('01-01-' . (date('Y') + 10));
        parent::__construct($options);

    }

    /**
     * @return array
     */
    public function getRequiredOptions()
    {
        return ['fieldName', 'message', 'groups'];
    }

    /**
     * @return mixed|string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * @return string
     */
    public function validatedBy()
    {
        return 'd_valid_date';
    }

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }

    /**
     * @param $fieldName
     * @return bool
     */
    public function hasOption($fieldName)
    {
        return !empty(isset($this->$fieldName));
    }

    /**
     * @return \DateTime
     */
    public function getMinDate()
    {
        return $this->minDate;
    }

    /**
     * @return \DateTime
     */
    public function getMaxDate()
    {
        return $this->maxDate;
    }
}
