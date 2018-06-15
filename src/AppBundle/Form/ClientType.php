<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientType extends AbstractType
{
    private $clientValidated = false;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (isset($options['client_validated'])) {
            $this->setClientValidated((bool) $options['client_validated']);
        }

        if ($this->isClientValidated()) {
            $builder->add('firstname', FormTypes\TextType::class, ['attr'=> ['readonly' => 'readonly']])
                ->add('lastname', FormTypes\TextType::class, ['attr'=> ['readonly' => 'readonly']])
                ->add('caseNumber', FormTypes\TextType::class, ['attr'=> ['readonly' => 'readonly']]);
        } else {
            $builder->add('firstname', FormTypes\TextType::class)
                ->add('lastname', FormTypes\TextType::class)
                ->add('caseNumber', FormTypes\TextType::class);
        }
        $builder->add('courtDate', FormTypes\DateType::class, [
            'widget' => 'text',
            'input' => 'datetime',
            'format' => 'yyyy-MM-dd',
            'invalid_message' => 'client.courtDate.message',
        ])
                ->add('address', FormTypes\TextType::class)
                ->add('address2', FormTypes\TextType::class)
                ->add('postcode', FormTypes\TextType::class)
                ->add('county', FormTypes\TextType::class)
                ->add('country', FormTypes\CountryType::class, [
                      'preferred_choices' => ['GB'],
                      'empty_value' => 'country.defaultOption',
                ])
                ->add('phone', FormTypes\TextType::class)
                ->add('id', FormTypes\HiddenType::class)
                ->add('save', FormTypes\SubmitType::class);

        // strip tags to prevent XSS as the name is often displayed around mixed with translation with the twig "raw" filter
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $data['firstname'] = strip_tags($data['firstname']);
            $data['lastname'] = strip_tags($data['lastname']);
            $event->setData($data);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'registration',
            'validation_groups'  => 'lay-deputy-client',
            'client_validated'   => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'client';
    }

    /**
     * @return bool
     */
    public function isClientValidated()
    {
        return $this->clientValidated;
    }

    /**
     * @param bool $clientValidated
     */
    public function setClientValidated($clientValidated)
    {
        $this->clientValidated = $clientValidated;
        return $this;
    }
}
