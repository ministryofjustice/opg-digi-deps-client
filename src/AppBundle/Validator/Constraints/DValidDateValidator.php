<?php

namespace AppBundle\Validator\Constraints;

use Aws\CloudFront\Exception\Exception;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class DValidDateValidator extends ConstraintValidator
{
    /**
     * @var integer
     */
    var $max_date;

    /**
     * @var integer
     */
    var $min_date;

    /**
     * @var Constraint
     */
    var $constraint;

    /**
     * Validates a date
     *
     * @param mixed $date
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        try {
            $this->validateDate($value);
            $this->validateDate($constraint->getMinDate());
            $this->validateDate($constraint->getMaxDate());
        } catch (\Exception $e) {
            $this->context->buildViolation($constraint->exceptionMessage)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->addViolation();
        }

        $this->checkDateRange($value, $constraint);

    }

    /**
     * Validates we have a valid DateTime object. Otherwise add violation
     * @param mixed $value
     */
    private function validateDate($value)
    {
        if (!$value instanceof \DateTime)
        {
            throw new Exception('Invalid date used when validating');
        }
    }

    /**
     * Checks date is within a year range (1900 -> current year +10)
     *
     * @param \DateTime $date
     * @param Constraint $constraint
     */
    private function checkDateRange(\DateTime $date, Constraint $constraint)
    {
        if ($date < $constraint->getMinDate()) {
            $this->context->addViolationAt(
                $constraint->getFieldName(),
                $constraint->minDateErrorMessage
            );
        }

        if ($date > $constraint->getMaxDate()) {
            $this->context->addViolationAt(
                $constraint->getFieldName(),
                $constraint->maxDateErrorMessage
            );
        }
    }

}
