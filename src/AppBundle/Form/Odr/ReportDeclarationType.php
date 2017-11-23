<?php

namespace AppBundle\Form\Odr;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReportDeclarationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('id', 'hidden')
                ->add('agree', 'checkbox', [
                     'constraints' => new NotBlank(['message' => 'report-declaration.agree.notBlank']),
                 ])
                 ->add('agreedBehalfDeputy', 'choice', [
                    'choices' => [
                        // api models contains those keys too. Change them accordingly if needed
                        'only_deputy' => 'agreedBehalfDeputy.only_deputy',
                        'more_deputies_behalf' => 'agreedBehalfDeputy.more_deputies_behalf',
                        'more_deputies_not_behalf' => 'agreedBehalfDeputy.more_deputies_not_behalf',
                    ],
                    'choice_translation_domain' => 'report-declaration',
                    'translation_domain' => 'report-declaration',
                    'expanded' => true,
                ])
                ->add('agreedBehalfDeputyExplanation', 'textarea')
                ->add('save', 'submit');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'odr-declaration',
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();
                $validationGroups = ['declare'];

                if ($data->getAgreedBehalfDeputy() == 'more_deputies_not_behalf') {
                    $validationGroups[] = 'declare-explanation';
                }

                return $validationGroups;
            },
        ]);
    }

    public function getName()
    {
        return 'odr_declaration';
    }
}
