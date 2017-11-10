<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Report\Report;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/elements")
 */
class ElementController extends AbstractController
{
    /**
     * @Route("", name="elements")
     * @Template("AppBundle:Element:index.html.twig")
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @Route("/base", name="elements_base")
     * @Template("AppBundle:Element:base.html.twig")
     */
    public function baseAction()
    {
        $report = new Report();
        $report->setId(1);
        $report->setType(Report::TYPE_102);

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/layout", name="elements_layout")
     * @Template("AppBundle:Element/layout:layout.html.twig")
     */
    public function layoutAction()
    {
        $breadCrumb = [
            ['label' => 'Digideps Elements', 'href' => $this->generateUrl('elements')],
            ['label' => 'Layout'],

        ];

        return [
            'breadCrumb' => $breadCrumb,
        ];
    }

    /**
     * @Route("/colour", name="elements_colour")
     * @Template("AppBundle:Element:colour.html.twig")
     */
    public function colourAction()
    {
        $breadCrumb = [
            ['label' => 'Digideps Elements', 'href' => $this->generateUrl('elements')],
            ['label' => 'Colours'],

        ];

        return [
            'breadCrumb' => $breadCrumb,
        ];
    }

    /**
     * @Route("/formcomponents", name="elements_form")
     * @Template("AppBundle:Element:forms.html.twig")
     */
    public function formComponentsAction() { return []; }

    /**
     * @Route("/hero", name="elements_hero")
     * @Template("AppBundle:Element:hero.html.twig")
     */
    public function heroAction()
    {
        $breadCrumb = [
            ['label' => 'Digideps Elements', 'href' => $this->generateUrl('elements')],
            ['label' => 'Hero elements'],

        ];

        return [
            'breadCrumb' => $breadCrumb,
        ];
    }

    /**
     * @Route("/headings", name="elements_headings")
     * @Template("AppBundle:Element:headings.html.twig")
     */
    public function headingsAction()
    {
        $breadCrumb = [
            ['label' => 'Digideps Elements', 'href' => $this->generateUrl('elements')],
            ['label' => 'Headings'],

        ];

        return [
            'breadCrumb' => $breadCrumb,
        ];
    }

    /**
     * @Route("/components", name="elements_components")
     * @Template("AppBundle:Element:components.html.twig")
     */
    public function componentsAction() { return []; }

    /**
     * @Route("/alerts", name="elements_alerts")
     * @Template("AppBundle:Element:alerts.html.twig")
     */
    public function alertsAction() { return []; }

    /**
     * @Route("/buttons", name="elements_buttons")
     * @Template("AppBundle:Element:buttons.html.twig")
     */
    public function buttonsAction() { return[]; }

    /**
     * @Route("/navigation", name="elements_navigation")
     * @Template("AppBundle:Element:navigation.html.twig")
     */
    public function navigationAction() { return []; }

}
