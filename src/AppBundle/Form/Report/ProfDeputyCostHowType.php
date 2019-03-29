<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\Report;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfDeputyCostHowType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transPrefix = 'howCharged.form.options.';

        $builder
            ->add('profDeputyCostsHowCharged', FormTypes\ChoiceType::class, [
                'choices'  => [
                    $transPrefix . 'fixed' => Report::PROF_DEPUTY_COSTS_TYPE_FIXED,
                    $transPrefix . 'assessed' => Report::PROF_DEPUTY_COSTS_TYPE_ASSESSED,
                    $transPrefix . 'both' => Report::PROF_DEPUTY_COSTS_TYPE_BOTH],
                'expanded' => true
            ])
            ->add('save', FormTypes\SubmitType::class, ['label' => 'save.label']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
             'validation_groups' => ['prof-deputy-costs-how-changed'],
             'translation_domain' => 'report-prof-deputy-costs',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'deputy_costs';
    }
}
