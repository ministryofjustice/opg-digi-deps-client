<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use AppBundle\Entity\Role;

class DecisionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder ->add('title', 'text')
                 ->add('description', 'text')
                ->add('decisionDate', 'date', [ 'widget' => 'text',
                                              'input' => 'datetime',
                                              'format' => 'yyyy-MM-dd',
                                              'invalid_message' => 'TODO'
                                            ])
                 ->add('clientInvolvedBoolean', 'choice', array(
                    'choices' => [1=>'Yes', 0=>'No'],
                    'expanded' => true
                  ))
                 ->add('clientInvolvedDetails', 'textarea')
                 ->add('save', 'submit');
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults( [
              'translation_domain' => 'report-decision'
        ]);
    }
    
    public function getName()
    {
        return 'decision';
    }
}
