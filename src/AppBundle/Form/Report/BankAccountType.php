<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\BankAccount;
use AppBundle\Form\Type\SortCodeType;
use AppBundle\Validator\Constraints\Chain;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class BankAccountType extends AbstractType
{
    private $step;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->step = (int) $options['step'];

        $builder->add('id', 'hidden');

        if ($this->step === 1) {
            $builder->add('accountType', 'choice', [
                'choices'     => BankAccount::$types,
                'expanded'    => true,
                'empty_value' => 'Please select',
            ]);
        }

        if ($this->step === 2) {
            $builder->add('bank', 'text', [
                'required' => false,
            ]);
            $builder->add('accountNumber', 'text', ['max_length' => 4]);
            $builder->add('sortCode', new SortCodeType(), [
                'error_bubbling' => false,
                'required'       => false,
                'constraints'    => new Chain([
                    'constraints' => [
                        new NotBlank(['message' => 'account.sortCode.notBlank', 'groups' => ['bank-account-sortcode']]),
                        new Type(['type' => 'numeric', 'message' => 'account.sortCode.type', 'groups' => ['bank-account-sortcode']]),
                        new Length(['min' => 6, 'max' => 6, 'exactMessage' => 'account.sortCode.length', 'groups' => ['bank-account-sortcode']]),
                    ],
                    'stopOnError' => true,
                    'groups'      => ['bank-account-sortcode'],
                ]),
            ]);
            $builder->add('isJointAccount', 'choice', [
                'choices'  => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
            ]);
        }

        if ($this->step === 3) {
            $builder->add('openingBalance', 'number', [
                'precision'       => 2,
                'grouping'        => true,
                'invalid_message' => 'account.openingBalance.type',
            ]);
            $builder->add('closingBalance', 'number', [
                'precision'       => 2,
                'grouping'        => true,
                'invalid_message' => 'account.closingBalance.type',
                'required'        => false,
            ]);
        }

        if ($this->step === 4) {
            $builder->add('isClosed', 'choice', [
                'choices'     => [true => 'Yes', false => 'No'],
                'expanded'    => true,
                'empty_value' => 'Please select',
            ]);
        }

        $builder->add('save', 'submit');
    }

    public function getName()
    {
        return 'account';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-bank-accounts',
            'validation_groups'  => function (FormInterface $form) {
                $step2Options = ['bank-account-number', 'bank-account-is-joint'];
                if ($form->getData()->requiresSortCode()) {
                    $step2Options[] = 'bank-account-sortcode';
                }
                if ($form->getData()->requiresBankName()) {
                    $step2Options[] = 'bank-account-name';
                }

                return [
                    1 => ['bank-account-type'],
                    2 => $step2Options,
                    3 => ['bank-account-opening-balance'],
                    4 => 'bank-account-is-closed'
                ][$this->step];
            },
        ])
        ->setRequired(['step']);
    }
}
