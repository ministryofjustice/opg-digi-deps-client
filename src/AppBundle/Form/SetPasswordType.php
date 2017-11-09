<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SetPasswordType extends AbstractType
{
    protected $options;

    public function __construct($options)
    {
        $this->options = $options;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('password', 'repeated', [
                    'type' => 'password',
                    'invalid_message' => $this->options['passwordMismatchMessage'],
                ])
                ->add('save', 'submit');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
              'translation_domain' => 'user-activate',
               'validation_groups' => ['user_set_password'],
        ]);
    }

    public function getName()
    {
        return 'set_password';
    }
}
