<?php

namespace AppBundle\Form\Pa;

use AppBundle\Entity as EntityDir;
use Common\Form\Elements\InputFilters\Text;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ClientContactType
 *
 * @package AppBundle\Form\Pa
 */
class ClientContactType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
//        $contact = $builder->getData();

        $builder
            ->add('id', 'hidden')
            ->add('firstName', 'text', ['required' => true])
            ->add('lastName' , 'text', ['required' => true])
            ->add('jobTitle' , 'text')
            ->add('phone'    , 'text', ['required' => true])
            ->add('email'    , 'text', ['required' => true])
            ->add('orgName'  , 'text')
            ->add('address1' , 'text')
            ->add('address2' , 'text')
            ->add('address3' , 'text')
            ->add('addressPostcode' , 'text')
            ->add('addressCountry', 'country', ['preferred_choices' => ['', 'GB'], 'empty_value' => 'Please select ...',])
            ;

        $builder->add('save', 'submit');
    }

    /**
     * Set default form options
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'validation_groups' => ['add_note'],
                'translation_domain' => 'client-contacts',
                'data-class' => EntityDir\ClientContact::class
            ]
        );
    }

//    /**
//     * Return list of translated categories from the Note entity
//     *
//     * @return array
//     */
//    private function getCategories()
//    {
//        $ret = [];
//
//        foreach (NoteEntity::$categories as $categoryId => $cagtegoryTrqnslationKey) {
//            $ret[$categoryId] = $this->translate('form.category.entries.' . $cagtegoryTrqnslationKey);
//        }
//
//        return $ret;
//    }

//    /**
//     * Wrapper call to translator
//     *
//     * @param $key
//     * @return string
//     */
//    private function translate($key)
//    {
//        return $this->translator->trans($key, [], 'client-notes');
//    }
}
