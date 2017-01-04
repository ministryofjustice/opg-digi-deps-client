<?php

namespace AppBundle\Controller\Odr;

use AppBundle\Form as FormDir;
use AppBundle\Service\OdrStatusService;
use AppBundle\Service\SectionValidator\Odr\ActionsValidator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\AbstractController;

class OtherInfoController extends AbstractController
{
    private static $jmsGroups = [
        'client-cot',
        'odr-action-more-info',
    ];


    /**
     * @Route("/odr/{odrId}/any-other-info", name="odr_other_info")
     * @Template()
     */
    public function startAction(Request $request, $odrId)
    {
        $odr = $this->getOdr($odrId, self::$jmsGroups);
        if ($odr->getActionMoreInfo() !== null) {
            return $this->redirectToRoute('odr_other_info_summary', ['odrId' => $odrId]);
        }

        return [
            'odr' => $odr,
        ];
    }

    /**
     * @Route("/odr/{odrId}/any-other-info/step/{step}", name="odr_other_info_step")
     * @Template()
     */
    public function stepAction(Request $request, $odrId, $step)
    {
        $totalSteps = 1; //only one step but convenient to reuse the "step" logic and keep things aligned/simple
        if ($step < 1 || $step > $totalSteps) {
            return $this->redirectToRoute('odr_other_info_summary', ['odrId' => $odrId]);
        }
        $odr = $this->getOdr($odrId, self::$jmsGroups);
        $fromPage = $request->get('from');

        /* @var $stepRedirector StepRedirector */
        $stepRedirector = $this->get('stepRedirector')
            ->setRoutes('odr_other_info', 'odr_other_info_step', 'odr_other_info_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)->setTotalSteps($totalSteps)
            ->setRouteBaseParams(['odrId' => $odrId]);

        $form = $this->createForm(new FormDir\Odr\OtherInfoType(), $odr);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {
            $data = $form->getData();
            $this->getRestClient()->put('odr/' . $odrId , $data, ['more-info']);

            if ($fromPage == 'summary') {
                $request->getSession()->getFlashBag()->add(
                    'notice',
                    'Answer edited'
                );
            }

            return $this->redirect($stepRedirector->getRedirectLinkAfterSaving());
        }

        return [
            'odr'       => $odr,
            'step'         => $step,
            'odrStatus' => new OdrStatusService($odr),
            'form'         => $form->createView(),
            'backLink'     => $stepRedirector->getBackLink(),
            'skipLink'     => $stepRedirector->getSkipLink(),
        ];
    }

    /**
     * @Route("/odr/{odrId}/any-other-info/summary", name="odr_other_info_summary")
     * @Template()
     */
    public function summaryAction(Request $request, $odrId)
    {
        $fromPage = $request->get('from');
        $odr = $this->getOdr($odrId, self::$jmsGroups);
        //$this->flagSectionStarted($odr, self::SECTION_ID);
        if ($odr->getActionMoreInfo() === null && $fromPage != 'skip-step') {
            return $this->redirectToRoute('odr_other_info', ['odrId' => $odrId]);
        }

        return [
            'comingFromLastStep' => $fromPage == 'skip-step' || $fromPage == 'last-step',
            'odr'             => $odr,
            'validator'          => new ActionsValidator($odr),
        ];
    }
}
