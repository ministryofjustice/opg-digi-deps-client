<?php

namespace AppBundle\Form\Ndr\Debt;

use AppBundle\Entity\Ndr\Ndr;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DebtsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'hidden')
            ->add('debts', 'collection', [
                'type' => new DebtSingleType(),
                'cascade_validation' => true,
            ])
            ->add('save', 'submit');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => Ndr::class,
            'validation_groups'  => ['debts'],
            'cascade_validation' => true,
            'translation_domain' => 'ndr-debts',
        ]);
    }

    public function getName()
    {
        return 'debt';
    }
}
