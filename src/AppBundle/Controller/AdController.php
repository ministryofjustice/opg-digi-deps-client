<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/ad")
 */
class AdController extends AbstractController
{
    /**
     * @Route("/", name="ad_homepage")
     * @Template
     */
    public function indexAction(Request $request)
    {
        $form = $this->createForm(new FormDir\AddUserType([
            'roleChoices' => [2=>'Lay Deputy'],
            'roleIdEmptyValue' => null,
            'roleIdSetTo' => 2,
        ]), new EntityDir\User());

        return [
            'form' => $form->createView(),
        ];
    }
}
