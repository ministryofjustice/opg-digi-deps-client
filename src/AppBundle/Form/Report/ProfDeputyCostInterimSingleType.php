<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\MoneyShortCategory;
use AppBundle\Entity\Report\ProfDeputyInterimCost;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ProfDeputyCostInterimSingleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date', FormTypes\DateType::class, ['widget' => 'text',
                'input' => 'datetime',
                'format' => 'dd-MM-yyyy',
                'invalid_message' => 'Enter a valid date',
                'constraints' => [
                    new Callback(
                        [
                            'value' => $this,
                            'callback' => 'validateDateWithinReportingPeriod',
                            'payload' => ['startDate' => $options['startDate'], 'endDate' => $options['endDate']]
                        ]
                    )
                ]
            ])
            ->add('amount', FormTypes\NumberType::class, [
                'scale' => 2,
                'grouping' => true,
                'error_bubbling' => false,
                'invalid_message' => 'profDeputyInterimCost.amount.notNumeric',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined(['startDate', 'endDate'])
            ->setDefaults([
            'data_class' => ProfDeputyInterimCost::class,
            'translation_domain' => 'report-prof-deputy-costs',
            'validation_groups' => ['prof-deputy-interim-costs'],
        ]);

    }

    public function getBlockPrefix()
    {
        return 'costs_interim';
    }

    // This is still ignored when trying to validate using an inline callback
    public function dateWithinReportingPeriod($object, ExecutionContextInterface $context, $payload)
    {
        if ($this->getDate() < $payload['startDate'] || $this->getDate() > $payload['endDate']) {
            $context->buildViolation('profDeputyInterimCost.date.notValid')->atPath('date')->addViolation();
        }
    }
}
