<?php

namespace AppBundle\Controller\Odr;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\AbstractController;

class IncomeBenefitsController extends AbstractController
{
    private static $odrJmsGroups = [
        'odr',
        'client',
        'client-cot',
        'odr-income-state-benefits',
        'odr-income-pension',
        'odr-income-damages',
        'odr-income-one-off',
    ];

    /**
     * @Route("/odr/{odrId}/finance/income-benefits", name="odr-income-benefits")
     *
     * @param int $odrId
     * @Template("AppBundle:Odr/Finance/IncomeBenefits:index.html.twig")
     *
     * @return array
     */
    public function indexAction(Request $request, $odrId)
    {
        $odr = $this->getOdr($odrId, self::$odrJmsGroups);
        if ($odr->getSubmitted()) {
            throw new \RuntimeException('Odr already submitted and not editable.');
        }

        // temp to test form
        $odr->setStateBenefits([
            new EntityDir\Odr\IncomeBenefit('contributionsBasedAllowance', true),
            new EntityDir\Odr\IncomeBenefit('housingBenefit', false),
            new EntityDir\Odr\IncomeBenefit('otherBenefits', true, true, 'santa claus money'),
        ])
            ->setReceiveStatePension('no')
            ->setReceiveOtherIncome('yes')
            ->setReceiveOtherIncomeDetails('brother sends money')
            ->setExpectCompensationDamages('yes')
            ->setOneOff([
                new EntityDir\Odr\IncomeBenefit('bequestOrInheritance', true),
                new EntityDir\Odr\IncomeBenefit('cashGiftReceived', false),
                new EntityDir\Odr\IncomeBenefit('refunds', true),
            ]);

        $form = $this->createForm(new FormDir\Odr\IncomeBenefitsType(), $odr);
        $form->handleRequest($request);

        if ($form->isValid()) {
            echo "<pre>";\Doctrine\Common\Util\Debug::dump($odr->getStateBenefits(), 4);die;
            $this->getRestClient()->put('odr/' . $odrId, $form->getData(), [
                'deserialise_group' => 'odr-state-',
            ]);

            return $this->redirect($this->generateUrl('odr-income-benefits', ['odrId' => $odrId]));
        }

        return [
            'odr' => $odr,
            'subsection' => 'incomeBenefits',
            'form' => $form->createView(),
        ];
    }
}
