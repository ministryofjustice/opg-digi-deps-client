<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\Gift;
use AppBundle\Entity\User;
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
            ]);

        $reportType = $options['report']->getType();

        if (!empty($options['report']->getBankAccountOptions()) && (in_array($reportType, ['102', '102-4']))) {
            $builder->add('bankAccountId', 'choice', [
                    'choices' => $options['report']->getBankAccountOptions(),
                    'empty_value' => 'Please select'
                ]);
        }

        $builder ->add('save', 'submit');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Gift::class,
            'validation_groups' => ['gift'],
            'translation_domain' => 'report-gifts',
        ])
        ->setRequired(['user', 'report']);
    }

    public function getBlockPrefix()
    {
        return 'gifts_single';
    }
}
