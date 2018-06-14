<?php

namespace AppBundle\Twig;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormView;
use Symfony\Component\Translation\TranslatorInterface;
use Twig_Environment;

class FormFieldsExtension extends \Twig_Extension
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction( 'form_input', [$this, 'renderFormInput'], ['needs_environment' => true]),
            new \Twig_SimpleFunction('form_submit', [$this, 'renderFormSubmit'], ['needs_environment' => true]),
            new \Twig_SimpleFunction('form_errors_list', [$this, 'renderFormErrorsList'], ['needs_environment' => true]),
            new \Twig_SimpleFunction('form_select', [$this, 'renderFormDropDown'], ['needs_environment' => true]),
            new \Twig_SimpleFunction(  'form_known_date', [$this, 'renderFormKnownDate'], ['needs_environment' => true]),
            new \Twig_SimpleFunction('form_sort_code', [$this, 'renderFormSortCode'], ['needs_environment' => true]),
            new \Twig_SimpleFunction('form_checkbox_group', [$this, 'renderCheckboxGroup'], ['needs_environment' => true]),
            new \Twig_SimpleFunction( 'form_checkbox', [$this, 'renderCheckboxInput'], ['needs_environment' => true]),
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
    public function renderFormInput(Twig_Environment $env, $element, $elementName, array $vars = [], $transIndex = null)
    {
        //generate input field html using variables supplied
        echo $env->render(
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
    public function renderCheckboxInput(Twig_Environment $env, $element, $elementName, array $vars = [], $transIndex = null)
    {
        echo $env->render(
            'AppBundle:Components/Form:_checkbox.html.twig',
            $this->getFormComponentTwigVariables($element, $elementName, $vars, $transIndex)
        );
    }

    /**
     * @DEPRECATED
     *
     * //TODO consider refactor using getFormComponentTwigVariables
     */
    public function renderCheckboxGroup(Twig_Environment $env, FormView $element, $elementName, $vars, $transIndex = null)
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
        echo $env->render('AppBundle:Components/Form:_checkboxgroup.html.twig', [
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
    public function renderCheckboxGroupNew(Twig_Environment $env, FormView $element, $elementName, $vars, $transIndex = null)
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
        echo $env->render('AppBundle:Components/Form:_checkboxgroup_new.html.twig', [
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
    public function renderFormDropDown(Twig_Environment $env, $element, $elementName, array $vars = [], $transIndex = null)
    {
        //generate input field html using variables supplied
        echo $env->render(
            'AppBundle:Components/Form:_select.html.twig',
            $this->getFormComponentTwigVariables($element, $elementName, $vars, $transIndex)
        );
    }

    public function renderFormKnownDate(Twig_Environment $env, $element, $elementName, array $vars = [], $transIndex = null)
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

        $html = $env->render('AppBundle:Components/Form:_known-date.html.twig', ['legendText' => $legendText,
                                                                                                'legendTextJS' => $legendTextJS,
                                                                                                'hintText' => $hintText,
                                                                                                'element' => $element,
                                                                                                'showDay' => $showDay,
                                                                                                'legendTextRaw' => !empty($vars['legendRaw']), ]);
        echo $html;
    }

    public function renderFormSortCode(Twig_Environment $env, $element, $elementName, array $vars = [], $transIndex = null)
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

        $html = $env->render('AppBundle:Components/Form:_sort-code.html.twig', ['legendText' => $legendText,
                                                                                                'hintText' => $hintText,
                                                                                                'element' => $element,
                                                                                              ]);
        echo $html;
    }

    /**
     * @param mixed  $element
     * @param string $elementName
     * @param array  $vars
     * @param int    $transIndex
     */
    public function renderFormSubmit(Twig_Environment $env, $element, $elementName, array $vars = [], $transIndex = null)
    {
        //lets get the translation for class and labelText
        $translationKey = (!is_null($transIndex)) ? $transIndex . '.' . $elementName : $elementName;
        $domain = $element->parent->vars['translation_domain'];

        //sort out labelText translation
        $labelText = isset($vars['labelText']) ? $vars['labelText'] : $this->translator->trans($translationKey . '.label', [], $domain);
        $buttonClass = isset($vars['buttonClass']) ? $vars['buttonClass'] : null;

        //generate input field html using variables supplied
        $html = $env->render('AppBundle:Components/Form:_button.html.twig',
            [
                'labelText' => $labelText,
                'element' => $element,
                'buttonClass' => $buttonClass,
            ]);

        echo $html;
    }


    /**
     * get form errors list and render them inside Components/Alerts:error_summary.html.twig
     * Usage: {{ form_errors_list(form) }}.
     *
     * @param FormView $form
     */
    public function renderFormErrorsList(Twig_Environment $env, FormView $form)
    {
        $formErrorMessages = $this->getErrorsFromFormViewRecursive($form);

        $html = $env->render('AppBundle:Components/Alerts:_validation-summary.html.twig', [
            'formErrorMessages' => $formErrorMessages,
            'formUncaughtErrors' => empty($form->vars['errors']) ? [] : $form->vars['errors'],
        ]);

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
