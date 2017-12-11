<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class DateAfterValidator extends ConstraintValidator
{
    /**
     * @var \DateTime
     */
    var $target;

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
            $this->validateDate($constraint->getTarget());
        } catch (\Exception $e) {
            $this->context->buildViolation($constraint->exceptionMessage)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->addViolation();
        }

        if ($value <= $constraint->getTarget()) {
            $this->context->addViolationAt(
                $constraint->getField(),
                $constraint->message
            );
        }
    }

    /**
     * Validates we have a valid DateTime object. Otherwise add violation
     *
     * @param $value
     * @throws \Exception
     */
    private function validateDate($value)
    {
        if (!$value instanceof \DateTime)
        {
            throw new \Exception('Invalid date used when validating');
        }
    }
}
