<?php

namespace AppBundle\Validator\Constraints;


use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class DateWithinReportingPeriod extends Constraint
{
    public $message = 'The date must be within the reporting period - "{{ startDate }}" - "{{ endDate }}"';
}