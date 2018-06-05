<?php

namespace AppBundle\Twig;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormView;
use Symfony\Component\Translation\TranslatorInterface;

class FormFieldsExtension extends \Twig_Extension
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var \Twig_Environment
     */
    private $environment;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function initRuntime(\Twig_Environment $environment)
    {
        parent::initRuntime($environment);
        $this->environment = $environment;
    }

    public function getFunctions()
    {
        return [
            'form_input' => new \Twig_Function_Method($this, 'renderFormInput'),
            'form_submit' => new \Twig_Function_Method($this, 'renderFormSubmit'),
            'form_errors_list' => new \Twig_Function_Method($this, 'renderFormErrorsList'),
            'form_select' => new \Twig_Function_Method($this, 'renderFormDropDown'),
            'form_known_date' => new \Twig_Function_Method($this, 'renderFormKnownDate'),
            'form_sort_code' => new \Twig_Function_Method($this, 'renderFormSortCode'),
            'form_checkbox_group' => new \Twig_Function_Method($this, 'renderCheckboxGroup'),
            'form_checkbox' => new \Twig_Function_Method($this, 'renderCheckboxInput'),
        ];
    }

    /**
     * @DEPRECATED
     * Renders form input field.
     *
     * @param mixed  $element
     * @param string $elementName
     * @param int    $transIndex
     * @param array  $vars
     */
    public function renderFormInput($element, $elementName, array $vars = [], $transIndex = null)
    {
        //generate input field html using variables supplied
        echo $this->environment->render(
            'AppBundle:Components/Form:_input.html.twig',
            $this->getFormComponentTwigVariables($element, $elementName, $vars, $transIndex)
        );
    }

    /**
     * Renders form checkbox field.
     *
     * @param mixed  $element
     * @param string $elementName
     * @param int    $transIndex
     * @param array  $vars
     */
    public function renderCheckboxInput($element, $elementName, array $vars = [], $transIndex = null)
    {
        echo $this->environment->render(
            'AppBundle:Components/Form:_checkbox.html.twig',
            $this->getFormComponentTwigVariables($element, $elementName, $vars, $transIndex)
        );
    }

    /**
     * @DEPRECATED
     *
     * //TODO consider refactor using getFormComponentTwigVariables
     */
    public function renderCheckboxGroup(FormView $element, $elementName, $vars, $transIndex = null)
    {
        //lets get the translation for hintText, labelClass and labelText
        $translationKey = (!is_null($transIndex)) ? $transIndex . '.' . $elementName : $elementName;
        $domain = $element->parent->vars['translation_domain'];

        //sort hint text translation
        if (isset($vars['hintText'])) {
            $hintText = $vars['hintText'];
        } else {
            $hintTextTrans = $this->translator->trans($translationKey . '.hint', [], $domain);
            $hintText = ($hintTextTrans != $translationKey . '.hint') ? $hintTextTrans : null;
        }

        if (isset($vars['legendText'])) {
            $legendText = $vars['legendText'];
        } else {

            //get legendText translation. Look for a .legend value, if there isn't one then try the top level
            $legendTextTrans = $this->translator->trans($translationKey . '.legend', [], $domain);

            if ($legendTextTrans != $translationKey . '.legend') {
                $legendText = $legendTextTrans;
            } else {
                $labelParams = isset($vars['labelParameters']) ? $vars['labelParameters'] : [];
                $legendTextTrans = $this->translator->trans($translationKey . '.label', $labelParams, $domain);
                if ($legendTextTrans != $translationKey . '.label') {
                    $legendText = $legendTextTrans;
                } else {
                    $legendText = null;
                }
            }
        }

         //generate input field html using variables supplied
        echo $this->environment->render('AppBundle:Components/Form:_checkboxgroup.html.twig', [
            'fieldSetClass' => isset($vars['fieldSetClass']) ? $vars['fieldSetClass'] : null,
            'formGroupClass' => isset($vars['formGroupClass']) ? $vars['formGroupClass'] : null,
            'legendText' => $legendText,
            'legendClass' => isset($vars['legendClass']) ? $vars['legendClass'] : null,
            'useFormGroup' => isset($vars['useFormGroup']) ? $vars['useFormGroup'] : true,
            'hintText' => $hintText,
            'element' => $element,
            'vertical' => isset($vars['vertical']) ? $vars['vertical'] : false,
            'items' => empty($vars['items']) ? [] : $vars['items'],
            'translationDomain' => $domain,
        ]);
    }

    /**
     * @DEPRECATED
     *
     */
    public function renderCheckboxGroupNew(FormView $element, $elementName, $vars, $transIndex = null)
    {
        //lets get the translation for hintText, labelClass and labelText
        $translationKey = (!is_null($transIndex)) ? $transIndex . '.' . $elementName : $elementName;
        $domain = $element->parent->vars['translation_domain'];

        //sort hint text translation
        if (isset($vars['hintText'])) {
            $hintText = $vars['hintText'];
        } else {
            $hintTextTrans = $this->translator->trans($translationKey . '.hint', [], $domain);
            $hintText = ($hintTextTrans != $translationKey . '.hint') ? $hintTextTrans : null;
        }

        if (isset($vars['legendText'])) {
            $legendText = $vars['legendText'];
        } else {

            //get legendText translation. Look for a .legend value, if there isn't one then try the top level
            $legendTextTrans = $this->translator->trans($translationKey . '.legend', [], $domain);

            if ($legendTextTrans != $translationKey . '.legend') {
                $legendText = $legendTextTrans;
            } else {
                $labelParams = isset($vars['labelParameters']) ? $vars['labelParameters'] : [];
                $legendTextTrans = $this->translator->trans($translationKey . '.label', $labelParams, $domain);
                if ($legendTextTrans != $translationKey . '.label') {
                    $legendText = $legendTextTrans;
                } else {
                    $legendText = null;
                }
            }
        }

        //generate input field html using variables supplied
        echo $this->environment->render('AppBundle:Components/Form:_checkboxgroup_new.html.twig', [
            'fieldSetClass' => isset($vars['fieldSetClass']) ? $vars['fieldSetClass'] : null,
            'legendText' => $legendText,
            'legendClass' => isset($vars['legendClass']) ? $vars['legendClass'] : null,
            'hintText' => $hintText,
            'element' => $element,
            'vertical' => isset($vars['vertical']) ? $vars['vertical'] : false,
            'items' => empty($vars['items']) ? [] : $vars['items'],
            'translationDomain' => $domain,
        ]);
    }

    /**
     * Renders form select element.
     *
     * @param mixed  $element
     * @param string $elementName
     * @param int    $transIndex
     * @param array  $vars
     */
    public function renderFormDropDown($element, $elementName, array $vars = [], $transIndex = null)
    {
        //generate input field html using variables supplied
        echo $this->environment->render(
            'AppBundle:Components/Form:_select.html.twig',
            $this->getFormComponentTwigVariables($element, $elementName, $vars, $transIndex)
        );
    }

    public function renderFormKnownDate($element, $elementName, array $vars = [], $transIndex = null)
    {
        //lets get the translation for class and labelText
        $translationKey = (!is_null($transIndex)) ? $transIndex . '.' . $elementName : $elementName;
        // read domain from Form ption 'translation_domain'
        $domain = $element->parent->vars['translation_domain'];

        $translationKey = (!is_null($transIndex)) ? $transIndex . '.' . $elementName : $elementName;
        if (isset($vars['showDay'])) {
            $showDay = $vars['showDay'];
        } else {
            $showDay = 'true';
        }

        //sort hint text translation
        $hintTextTrans = $this->translator->trans($translationKey . '.hint', [], $domain);
        $hintText = ($hintTextTrans != $translationKey . '.hint') ? $hintTextTrans : null;

        //get legendText translation
        $legendParams = isset($vars['legendParameters']) ? $vars['legendParameters'] : [];

        $legendTextTrans = $this->translator->trans($translationKey . '.legend', $legendParams, $domain);

        if ($legendTextTrans != $translationKey . '.legend') {
            $legendText = $legendTextTrans;
        } else {
            // the
            $legendTextTrans = $this->translator->trans($translationKey . '.label', $legendParams, $domain);
            if ($legendTextTrans != $translationKey . '.label') {
                $legendText = $legendTextTrans;
            } else {
                $legendText = null;
            }
        }

        $legendTextTransJS = $this->translator->trans($translationKey . '.legendjs', $legendParams, $domain);
        $legendTextJS = ($legendTextTransJS != $translationKey . '.legendjs') ? $legendTextTransJS : null;

        $html = $this->environment->render('AppBundle:Components/Form:_known-date.html.twig', ['legendText' => $legendText,
                                                                                                'legendTextJS' => $legendTextJS,
                                                                                                'hintText' => $hintText,
                                                                                                'element' => $element,
                                                                                                'showDay' => $showDay,
                                                                                                'legendTextRaw' => !empty($vars['legendRaw']), ]);
        echo $html;
    }

    public function renderFormSortCode($element, $elementName, array $vars = [], $transIndex = null)
    {
        //lets get the translation for class and labelText
        $translationKey = (!is_null($transIndex)) ? $transIndex . '.' . $elementName : $elementName;
        // read domain from Form ption 'translation_domain'
        $domain = $element->parent->vars['translation_domain'];

        //sort hint text translation
        $hintTextTrans = $this->translator->trans($translationKey . '.hint', [], $domain);
        $hintText = ($hintTextTrans != $translationKey . '.hint') ? $hintTextTrans : null;

        //get legendText translation
        $legendTextTrans = $this->translator->trans($translationKey . '.legend', [], $domain);

        $legendText = ($legendTextTrans != $translationKey . '.legend') ? $legendTextTrans : null;

        $html = $this->environment->render('AppBundle:Components/Form:_sort-code.html.twig', ['legendText' => $legendText,
                                                                                                'hintText' => $hintText,
                                                                                                'element' => $element,
                                                                                              ]);
        echo $html;
    }

    /**
     * @param mixed  $element
     * @param string $elementName used to pick the translation by appending ".label"
     * @param array  $vars [buttonClass => additional class. "disabled" supported]
     */
    public function renderFormSubmit($element, $elementName, array $vars = [])
    {
        $options = [
            // label comes from labelText (if defined, but throws warning) ,or elementname.label from the form translation domain
            'label' => $elementName . '.label',
            'element' => $element,
            'translationDomain' => isset($vars['labelTranslationDomain']) ? $vars['labelTranslationDomain'] : null,
            'buttonClass' => isset($vars['buttonClass']) ? $vars['buttonClass'] : null,
        ];

        // deprecated. only kept in order not to break forms that use it
        if (isset($vars['labelText'])) {
            $options['label'] = $vars['labelText'];
        }

        $html = $this->environment->render('AppBundle:Components/Form:_button.html.twig', $options);

        echo $html;
    }

    /**
     * @param FormView $elementsFormView
     *
     * @return array
     */
    private function getErrorsFromFormViewRecursive(FormView $elementsFormView)
    {
        $ret = [];
        foreach ($elementsFormView as $elementFormView) {
            $elementFormErrors = empty($elementFormView->vars['errors']) ? [] : $elementFormView->vars['errors'];
            foreach ($elementFormErrors as $formError) { /* @var $error FormError */
                $ret[] = ['elementId' => $elementFormView->vars['id'], 'message' => $formError->getMessage()];
            }
            $ret = array_merge(
                $ret,
                $this->getErrorsFromFormViewRecursive($elementFormView)
            );
        }

        return $ret;
    }

    /**
     * get form errors list and render them inside Components/Alerts:error_summary.html.twig
     * Usage: {{ form_errors_list(form) }}.
     *
     * @param FormView $form
     */
    public function renderFormErrorsList(FormView $form)
    {
        $formErrorMessages = $this->getErrorsFromFormViewRecursive($form);

        $html = $this->environment->render('AppBundle:Components/Alerts:_validation-summary.html.twig', [
            'formErrorMessages' => $formErrorMessages,
            'formUncaughtErrors' => empty($form->vars['errors']) ? [] : $form->vars['errors'],
        ]);

        echo $html;
    }

    /**
     * @param \Symfony\Component\Form\FormView $element
     * @param string                           $elementName
     * @param array                            $vars
     * @param string|null                      $transIndex
     *
     * @return array with vars labelText,labelParameters,hintText,element,labelClass, to pass into twig templates AppBundle:Components/Form:*
     */
    private function getFormComponentTwigVariables($element, $elementName, array $vars, $transIndex)
    {
        //lets get the translation for hintText, labelClass and labelText
        $translationKey = (!is_null($transIndex)) ? $transIndex . '.' . $elementName : $elementName;
        $domain = $element->parent->vars['translation_domain'];

        if (isset($vars['hintText'])) {
            $hintText = $vars['hintText'];
        } else {
            $hintTextTrans = $this->translator->trans($translationKey . '.hint', [], $domain);
            $hintText = ($hintTextTrans != $translationKey . '.hint') ? $hintTextTrans : null;
        }

        //sort hintList text translation
        $hintListTextTrans = $this->translator->trans($translationKey . '.hintList', [], $domain);
        $hintListEntriesText = ($hintListTextTrans != $translationKey . '.hintList') ? array_filter(explode("\n", $hintListTextTrans)) : [];

        //sort out labelText translation
        $labelParams = isset($vars['labelParameters']) ? $vars['labelParameters'] : [];
        $labelText = isset($vars['labelText']) ? $vars['labelText'] : $this->translator->trans($translationKey . '.label', $labelParams, $domain);

        //inputPrefix
        $inputPrefix = isset($vars['inputPrefix']) ? $this->translator->trans($vars['inputPrefix'], [], $domain) : null;

        $labelClass = isset($vars['labelClass']) ? $vars['labelClass'] : null;
        $inputClass = isset($vars['inputClass']) ? $vars['inputClass'] : null;
        $formGroupClass = isset($vars['formGroupClass']) ? $vars['formGroupClass'] : '';

        //Text to insert to the left of an input, e.g. * * * * for account
        $preInputTextTrans = $this->translator->trans($translationKey . '.preInput', [], $domain);
        $preInputText = ($preInputTextTrans != $translationKey . '.preInput') ? $preInputTextTrans : null;

        return [
            'labelDataTarget' => empty($vars['labelDataTarget']) ? null : $vars['labelDataTarget'],
            'labelText' => $labelText,
            'hintText' => $hintText,
            'hintListArray' => $hintListEntriesText,
            'element' => $element,
            'labelClass' => $labelClass,
            'inputClass' => $inputClass,
            'inputPrefix' => $inputPrefix,
            'useFormGroup' => isset($vars['useFormGroup']) ? $vars['useFormGroup'] : true,
            'formGroupClass' => $formGroupClass,
            'labelRaw' => !empty($vars['labelRaw']),
            'preInputText' => $preInputText,
        ];
    }

    public function getName()
    {
        return 'form_input_extension';
    }
}
