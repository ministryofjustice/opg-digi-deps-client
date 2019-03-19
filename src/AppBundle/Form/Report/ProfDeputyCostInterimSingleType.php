<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\MoneyShortCategory;
use AppBundle\Entity\Report\ProfDeputyInterimCost;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

class ProfDeputyCostInterimSingleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date', FormTypes\DateType::class, ['widget' => 'text',
                'input' => 'datetime',
                'format' => 'dd-MM-yyyy',
                'invalid_message' => 'Enter a valid date',
                'constraints' => new Range(['min' => $options['startDate']->format('dd-MM-yyyy'), 'max' => $options['endDate']->format('dd-MM-yyyy')]),
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
}
