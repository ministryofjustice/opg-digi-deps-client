<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\Gift;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GiftType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('explanation', 'textarea', [
                'required' => true,
            ])
            ->add('amount', 'number', [
                'precision' => 2,
                'grouping' => true,
                'invalid_message' => 'gifts.amount.type',
            ])
            ->add('save', 'submit');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Gift::class,
            'validation_groups' => ['gift'],
            'translation_domain' => 'report-gifts',
        ]);
    }

    public function getName()
    {
        return 'gifts_single';
    }
}
