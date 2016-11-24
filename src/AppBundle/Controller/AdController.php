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
        $orderBy = $request->query->has('order_by') ? $request->query->get('order_by') : 'firstname';
        $sortOrder = $request->query->has('sort_order') ? $request->query->get('sort_order') : 'ASC';
        $limit = $request->query->get('limit') ?: 50;
        $offset = $request->query->get('offset') ?: 0;
        $userCount = $this->getRestClient()->get('user/count', 'array');
        $users = $this->getRestClient()->get("user/get-all/{$orderBy}/{$sortOrder}/$limit/$offset/1", 'User[]');
        $newSortOrder = $sortOrder == 'ASC' ? 'DESC' : 'ASC';

        return [
            'users' => $users,
            'userCount' => $userCount,
            'limit' => $limit,
            'offset' => $offset,
            'newSortOrder' => $newSortOrder,
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

            // flag as managed in order to retrieve it later
            $deputy->setAdManaged(true);
            $this->getRestClient()->put('user/'.$deputy->getId(), $deputy, ['ad_managed']);

            // recreate token needed for login
            $deputy = $this->getRestClient()->userRecreateToken($deputy->getEmail());

            // redirect to deputy area
            $deputyBaseUrl = rtrim($this->container->getParameter('non_admin_host'), '/');
            $redirectUrl = $deputyBaseUrl . $this->generateUrl('ad_login', [
                    'adId' => $adId,
                    'userToken' => $deputy->getRegistrationToken(),
                    'adFirstname' => $deputy->getFirstname(),
                    'adLastname' => $deputy->getLastname(),
                ]);

            return $this->redirect($redirectUrl);


        } catch (\Exception $e) {
            return $this->render('AppBundle:Admin:error.html.twig', [
                'error' => $e->getMessage(),
            ]);
        }


    }
}
