<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

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
        return [
        ];
    }

    /**
     * @Route("/view-user", name="ad_view_user")
     * @Method({"GET", "POST"})
     * @Template
     *
     * @param Request $request
     */
    public function viewUserAction(Request $request)
    {
        $what = $request->get('what');
        $filter = $request->get('filter');

        try {
            $user = $this->getRestClient()->get("user/get-one-by/{$what}/{$filter}", 'User', ['user', 'role', 'client', 'report', 'odr']);
        } catch (\Exception $e) {
            return $this->render('AppBundle:Ad:error.html.twig', [
                'error' => 'User not found',
            ]);
        }

        return [
            'action' => 'edit',
            'id' => $user->getId(),
            'user' => $user,
        ];
    }

    /**
     * @Route("/login-as-deputy/{deputyId}", name="ad_deputy_login_redirect")
     *
     * @param Request $request
     */
    public function adLoginAsDeputyAction(Request $request, $deputyId)
    {
        $adId = $this->getUser()->getId();

        // get user and check it's deputy and ODR
        try {
            /* @var $deputy EntityDir\User */
            $deputy = $this->getRestClient()->get("user/get-one-by/user_id/{$deputyId}", 'User', ['user', 'role']);
            if ($deputy->getRole()['role'] != EntityDir\Role::LAY_DEPUTY) {
                throw new \RuntimeException('User not a Lay deputy');
            }
            if (!$deputy->isOdrEnabled()) {
                throw new \RuntimeException('User not ODR enabled');
            }

            $deputy = $this->getRestClient()->userRecreateToken($deputy->getEmail());

            $deputyBaseUrl = rtrim($this->container->getParameter('non_admin_host'), '/');
            $redirectUrl = $deputyBaseUrl . $this->generateUrl('ad_login', [
                    'adId' => $adId,
                    'userToken' => $deputy->getRegistrationToken(),
                ]);

            return $this->redirect($redirectUrl);


        } catch (\Exception $e) {
            return $this->render('AppBundle:Admin:error.html.twig', [
                'error' => $e->getMessage(),
            ]);
        }


    }
}
