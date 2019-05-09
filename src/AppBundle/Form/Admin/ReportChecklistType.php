<?php

namespace AppBundle\Form\Admin;

use AppBundle\Entity\Report\Checklist;
use AppBundle\Entity\Report\Report;
use Doctrine\Common\Util\Debug;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReportChecklistType extends AbstractType
{
    const SAVE_ACTION = 'submitAndDownload';
    const SUBMIT_AND_DOWNLOAD_ACTION = 'submitAndDownload';

    /**
     * @var Report
     */
    private $report;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $finalDecisionTransPrefix = 'checklistPage.form.finalDecision.options.';
        $this->report = $options['report'];


        // HW & PFA
        $builder
            ->add('id', FormTypes\HiddenType::class)
            ->add('reportingPeriodAccurate', FormTypes\ChoiceType::class, [
                'choices' => ['Yes' => 'yes', 'No' => 'no'],
                'expanded' => true
            ])
            ->add('contactDetailsUptoDate', FormTypes\CheckboxType::class, []);

        // DDPB-2293 question not relevant for PA
        if (!$this->report->hasSection('paDeputyExpenses')) {
            $builder->add('deputyFullNameAccurateInCasrec', FormTypes\CheckboxType::class, []);
        }

            // Decisions
        $builder->add('decisionsSatisfactory', FormTypes\ChoiceType::class, [
                'choices' => ['Yes' => 'yes', 'No' => 'no'],
                'expanded' => true
            ])

            // People Consulted
            ->add('consultationsSatisfactory', FormTypes\ChoiceType::class, [
                'choices' => ['Yes' => 'yes', 'No' => 'no'],
                'expanded' => true
            ])

            // Visits and Care
            ->add('careArrangements', FormTypes\ChoiceType::class, [
                'choices' => ['Yes' => 'yes', 'No' => 'no'],
                'expanded' => true
            ]);

        // PFA
        if($this->report->hasSection('bankAccounts')) {
            // Client Assets and Debt
            $builder
                ->add('assetsDeclaredAndManaged', FormTypes\ChoiceType::class, [
                    'choices' => ['Yes' => 'yes', 'No' => 'no', 'Not applicable' => 'na'],
                    'expanded' => true
                ])
                ->add('debtsManaged', FormTypes\ChoiceType::class, [
                    'choices' => ['Yes' => 'yes', 'No' => 'no', 'Not applicable' => 'na'],
                    'expanded' => true
                ])

                // Money In
                ->add('openClosingBalancesMatch', FormTypes\ChoiceType::class, [
                    'choices' => ['Yes' => 'yes', 'No' => 'no', 'Not applicable' => 'na'],
                    'expanded' => true
                ])
                ->add('accountsBalance', FormTypes\ChoiceType::class, [
                    'choices' => ['Yes' => 'yes', 'No' => 'no', 'Not applicable' => 'na'],
                    'expanded' => true
                ])
                ->add('moneyMovementsAcceptable', FormTypes\ChoiceType::class, [
                    'choices' => ['Yes' => 'yes', 'No' => 'no'],
                    'expanded' => true
                ]);

            // If PA report, add PA deputy Expenses question
            if ($this->report->hasSection('paDeputyExpenses')) {
                $builder
                    ->add('satisfiedWithPaExpenses', FormTypes\ChoiceType::class, [
                        'choices' => ['Yes' => 'yes', 'No' => 'no', 'Not applicable' => 'na'],
                        'expanded' => true
                    ])
                    ->add('deputyChargeAllowedByCourt', FormTypes\ChoiceType::class, [
                        'choices' => ['Yes' => 'yes', 'No' => 'no'],
                        'expanded' => true
                    ]);
            } else {
                // Otherwise add Bonds question
                $builder->add('bondAdequate', FormTypes\ChoiceType::class, [
                    'choices' => ['Yes' => 'yes', 'No' => 'no', 'Not applicable' => 'na'],
                    'expanded' => true
                ]);
                $builder->add('bondOrderMatchCasrec', FormTypes\ChoiceType::class, [
                    'choices' => ['Yes' => 'yes', 'No' => 'no', 'Not applicable' => 'na'],
                    'expanded' => true
                ]);
            }
        }

        if ($this->report->hasSection('profDeputyCosts')) {
            $builder->add('paymentsMatchCostCertificate', FormTypes\ChoiceType::class, [
                'choices' => ['Yes' => 'yes', 'No' => 'no'],
                'expanded' => true
            ]);
            $builder->add('profCostsReasonableAndProportionate', FormTypes\ChoiceType::class, [
                'choices' => ['Yes' => 'yes', 'No' => 'no'],
                'expanded' => true
            ]);

            $builder->add('hasDeputyOverchargedFromPreviousEstimates', FormTypes\ChoiceType::class, [
                'choices' => ['Yes' => 'yes', 'No' => 'no'],
                'expanded' => true
            ]);
        }

        if ($this->report->hasSection('profDeputyCostsEstimate')) {
            $builder->add('nextBillingEstimatesSatisfactory', FormTypes\ChoiceType::class, [
                'choices' => ['Yes' => 'yes', 'No' => 'no'],
                'expanded' => true
            ]);
        }

        // HW
        if($this->report->hasSection('lifestyle')) {
            // Health and Lifestyle question
            $builder->add('satisfiedWithHealthAndLifestyle', FormTypes\ChoiceType::class, [
                'choices' => ['Yes' => 'yes', 'No' => 'no'],
                'expanded' => true
            ]);
        }

        // HW and PFA

        // Next reporting period
        $builder
            ->add('futureSignificantDecisions', FormTypes\ChoiceType::class, [
                'choices' => ['Yes' => 'yes', 'No' => 'no'],
                'expanded' => true
            ])
            ->add('hasDeputyRaisedConcerns', FormTypes\ChoiceType::class, [
                'choices' => ['Yes' => 'yes', 'No' => 'no'],
                'expanded' => true
            ])

            // Deputy declaration
            ->add('caseWorkerSatisified', FormTypes\ChoiceType::class, [
                'choices' => ['Yes' => 'yes', 'No' => 'no'],
                'expanded' => true
            ])

            // Lodging summary
            ->add('lodgingSummary', FormTypes\TextareaType::class, [])

            // Final decision
            ->add('finalDecision', FormTypes\ChoiceType::class, [
                'choices' => [
                    $finalDecisionTransPrefix . 'forReview' => 'for-review',
                    $finalDecisionTransPrefix . 'incomplete' =>  'incomplete',
                    $finalDecisionTransPrefix . 'furtherCaseworkRequired' => 'further-casework-required',
                    $finalDecisionTransPrefix . 'satisfied' => 'satisfied'
                ],
                'expanded' => true
            ])

            // Further information received
            ->add('furtherInformationReceived', FormTypes\TextareaType::class, [])
            ->add('saveFurtherInformation', FormTypes\SubmitType::class)
            ->add('save', FormTypes\SubmitType::class)
            ->add('submitAndDownload', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'admin-checklist',
            'data-class' => Checklist::class,
            'name'               => 'checklist',
            'validation_groups'  => function (FormInterface $form) {
                $ret = [];
                if (self::SUBMIT_AND_DOWNLOAD_ACTION == $form->getClickedButton()->getName()) {
                     $ret[] = 'submit-common-checklist';

                    $sectionsToValidate = $this->report->getAvailableSections();
                    $isPaReport = $this->report->hasSection('paDeputyExpenses');
                    $hasFinanceInfo = $this->report->hasSection('bankAccounts');

                    foreach ($sectionsToValidate as $section_id) {
                        $ret[] = 'submit-' . $section_id . '-checklist';
                    }

                    // DDPB-2293 question not relevant for PA
                    if (!$isPaReport) {
                        $ret[] = 'submit-deputy-fullname-accurate-casrec-checklist';
                    }

                    // bonds to show when report has financial info but not a PA one
                    if ($hasFinanceInfo && !$isPaReport) {
                        $ret[] = 'submit-bonds-checklist';
                    }


                }
                return $ret;
            },
        ])
        ->setRequired(['report'])
        ;
    }
}
