<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CoDeputyVerificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('firstname', 'text')
            ->add('lastname', 'text')
            ->add('address1', 'text')
            ->add('address2', 'text')
            ->add('address3', 'text')
            ->add('addressPostcode', 'text')
            ->add('addressCountry', 'country', [
                'preferred_choices' => ['', 'GB'],
                'empty_value' => 'Please select ...',
            ])
            ->add('phoneMain', 'text')
            ->add('phoneAlternative', 'text')
            ->add('email', 'text')
            ->add('clientLastname', 'text', ['mapped' => false])
            ->add('clientCaseNumber', 'text', ['mapped' => false])
            ->add('save', 'submit');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'co-deputy',
            'validation_groups' => ['verify-codeputy'],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'co_deputy';
    }
}
