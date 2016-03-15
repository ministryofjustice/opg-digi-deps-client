<?php

namespace AppBundle\Form\Asset;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormInterface;

class AssetTypeProperty extends AbstractAssetType
{


    protected function addFields($builder, $options)
    {
        $builder
            ->add('address', 'text')
            ->add('address2', 'text')
            ->add('postcode', 'text')
            ->add('county', 'text')
            ->add('occupants', 'textarea')
            ->add('owned', 'choice', array(
                'choices' => ['fully' => 'Fully owned', 'partly' => 'Part-owned'],
                'expanded' => true
            ))
            ->add('ownedPercentage', 'text') //only if owned=partly
            ->add('isSubjectToEquityRelease', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true
            ])
            ->add('value', 'number', [
                'grouping' => true,
                'precision' => 2,
                'invalid_message' => 'asset.value.type'
            ])
            ->add('hasMortgage', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true
            ])
            ->add('mortgageOutstandingAmount', 'text') //only if hasMortgage=yes
            ->add('hasCharges', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true
            ])
            ->add('isRentedOut', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true
            ])
//                ->add('rentAgreementEndDate', 'date', [ 
//                    'widget' => 'text',
//                    'input' => 'datetime',
//                    'format' => 'dd-MM-yyyy',
//                    'invalid_message' => 'Enter a valid date',
//                    'required' => false
//                ])
            ->add('rentIncomeMonth', 'text')


        ;
    }


    protected function getValidationGroups()
    {
        return function(FormInterface $form) {

             /** @var $data \AppBundle\Entity\AssetProperty */
            $data = $form->getData();
            $validationGroups = ['property'];

            if ($data->getOwned() == "partly") {
                $validationGroups[] = "owned-partly";
            }

            
            if ($data->getHasMortgage() == 'yes') {
                $validationGroups[] = "mortgage-yes";
            }
            
            if ($data->getIsRentedOut() == 'yes') {
                $validationGroups[] = "rented-out-yes";
            }

            return $validationGroups;
        };
    }

}