<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\VisitsCare;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class VisitsCareType extends AbstractType
{
    /**
     * @var int
     */
    private $step;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var string
     */
    private $clientFirstName;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->step            = (int) $options['step'];
        $this->translator      = $options['translator'];
        $this->clientFirstName = $options['clientFirstName'];

        if ($this->step === 1) {
            $builder->add('doYouLiveWithClient', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
            ]);
            $builder->add('howOftenDoYouContactClient', 'textarea');
        }

        if ($this->step === 2) {
            $builder->add('doesClientReceivePaidCare', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
            ]);

            $builder->add('howIsCareFunded', 'choice', [
                'choices' => [
                    'client_pays_for_all' => $this->translate('form.howIsCareFunded.choices.client_pays_for_all'),
                    'client_gets_financial_help' => $this->translate('form.howIsCareFunded.choices.client_gets_financial_help'),
                    'all_care_is_paid_by_someone_else' => $this->translate('form.howIsCareFunded.choices.all_care_is_paid_by_someone_else'),
                ],
                'expanded' => true,
            ]);
        }

        if ($this->step === 3) {
            $builder->add('whoIsDoingTheCaring', 'textarea');
        }

        if ($this->step === 4) {
            $builder->add('doesClientHaveACarePlan', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
            ]);

            $builder->add('whenWasCarePlanLastReviewed', 'date', ['widget' => 'text',
                'input' => 'datetime',
                'format' => 'dd-MM-yyyy',
                'invalid_message' => 'visitsCare.whenWasCarePlanLastReviewed.invalidMessage',
            ]);
        }
        $builder->add('save', 'submit');


        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();

            if ($this->step == 4) {
                // Strip out the date field if it's not needed. Having a partial date field breaks things
                // if the care plan date is hidden as it receives a date that only has a day
                if (isset($data['doesClientHaveACarePlan']) && $data['doesClientHaveACarePlan'] == 'no') {
                    $data['whenWasCarePlanLastReviewed'] = null;
                }

                // whenWasCarePlanLastReviewed: set day=01 if month and year are set
                if (!empty($data['whenWasCarePlanLastReviewed']['month']) && !empty($data['whenWasCarePlanLastReviewed']['year'])) {
                    $data['whenWasCarePlanLastReviewed']['day'] = '01';
                    $event->setData($data);
                }
            }

            $event->setData($data);
        });
    }

    private function translate($key)
    {
        return $this->translator->trans($key, ['%client%' => $this->clientFirstName], 'report-visits-care');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-visits-care',
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();
                /* @var $data VisitsCare */
                $validationGroups = [
                    1 => ($data->getDoYouLiveWithClient() == 'no')
                        ? ['visits-care-live-client', 'visits-care-how-often-contact']
                        : ['visits-care-live-client'],
                    2=> ($data->getDoesClientReceivePaidCare() == 'yes')
                    ? ['visits-care-receive-paid-care', 'visits-care-how-care-funded']
                    : ['visits-care-receive-paid-care'],
                    3=> ['visits-care-who-does-caring'],
                    4=> ($data->getDoesClientHaveACarePlan() == 'yes')
                        ?['visits-care-have-care-plan', 'visits-care-care-plan-last-review']
                        :['visits-care-have-care-plan'],
                ][$this->step];

                return $validationGroups;
            },
        ])
        ->setRequired(['step', 'translator', 'clientFirstName'])
        ->setAllowedTypes('translator', TranslatorInterface::class);
    }

    public function getName()
    {
        return 'visits_care';
    }
}
