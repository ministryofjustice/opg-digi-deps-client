<?php

namespace AppBundle\Validator\Constraints;


use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class DateWithinReportingPeriodValidator extends ConstraintValidator
{

    /**
     * @param mixed $data
     * @param Constraint $constraint
     */
    public function validate($data, Constraint $constraint)
    {
        if (!$constraint instanceof DateWithinReportingPeriod) {
            throw new UnexpectedTypeException($constraint, DateWithinReportingPeriod::class);
        }

        if (null === $data || '' === $data) {
            return;
        }

        // Validate against start and end dates here...but how to get the Report when the $data will be ProfDeputyInterimCost?
    }
}