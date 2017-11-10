<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Constraints;

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
            ]);

        if (!empty($this->options['showTermsAndConditions'])) {
            $builder->add('showTermsAndConditions', 'checkbox', [
                'mapped'=>false,
                'constraints' => [new Constraints\NotBlank(['message' => 'user.agreeTermsUse.notBlank', 'groups'=>['user_set_password']])]
            ]);
        }
        $builder->add('save', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
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
