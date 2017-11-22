<?php

namespace AppBundle\Form;

use AppBundle\Validator\Constraints\DUserPassword;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ChangePasswordType extends AbstractType
{
    const VALIDATION_GROUP = 'change_password';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('current_password', 'password', [
                    'mapped' => false,
                    'constraints' => [
                        new Assert\NotBlank(['message' => 'user.password.existing.notBlank', 'groups' => [self::VALIDATION_GROUP]]),
                        new DUserPassword(['message' => 'user.password.existing.notCorrect', 'groups' => [self::VALIDATION_GROUP]]),
                    ],
                ])
                ->add('plain_password', 'repeated', [
                    'mapped' => false,
                    'type' => 'password',
                    'invalid_message' => 'user.password.new.doesntMatch',
                    'constraints' => [
                        new Assert\NotBlank(['message' => 'user.password.new.notBlank', 'groups' => [self::VALIDATION_GROUP]]),
                        new Assert\Length(['min' => 8, 'max' => 50, 'minMessage' => 'user.password.minLength', 'maxMessage' => 'user.password.maxLength', 'groups' => [self::VALIDATION_GROUP]]),
                        new Assert\Regex(['pattern' => '/[a-z]/', 'message' => 'user.password.noLowerCaseChars', 'groups' => self::VALIDATION_GROUP]),
                        new Assert\Regex(['pattern' => '/[A-Z]/', 'message' => 'user.password.noUpperCaseChars', 'groups' => self::VALIDATION_GROUP]),
                        new Assert\Regex(['pattern' => '/[0-9]/', 'message' => 'user.password.noNumber', 'groups' => self::VALIDATION_GROUP]),
                    ],
                ])
                ->add('id', 'hidden')
                ->add('save', 'submit');
    }

    public function getParent()
    {
        return 'form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'settings',
            'validation_groups' => [self::VALIDATION_GROUP],
        ]);
    }

    public function getName()
    {
        return 'change_password';
    }
}
