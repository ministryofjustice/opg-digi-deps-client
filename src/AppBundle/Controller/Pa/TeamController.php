<?php

namespace AppBundle\Controller\Pa;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Exception\RestClientException;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/pa/settings/user-accounts")
 */
class TeamController extends AbstractController
{
    /**
     * @Route("", name="pa_team")
     * @Template
     */
    public function listAction(Request $request)
    {
        $teamMembers = $this->getRestClient()->get('team/members', 'User[]');

        return [
            'teamMembers' => $teamMembers
        ];
    }

    /**
     * @Route("/add", name="add_team_member")
     * @Template()
     */
    public function addAction(Request $request)
    {
        $this->denyAccessUnlessGranted('add-user', null, 'Access denied');

        $team = $this->getRestClient()->get('user/' . $this->getUser()->getId() . '/team', 'Team');
        $validationGroups = $team->canAddAdmin() ? ['pa_team_add', 'pa_team_role_name'] : ['pa_team_add'];

        $form = $this->createForm(FormDir\Pa\TeamMemberAccountType::class, null, [ 'team'              => $team, 'loggedInUser'      => $this->getUser(), 'validation_groups' => $validationGroups
                                   ]
                                 );

        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var $user EntityDir\User */
            $user = $form->getData();

            if ($this->isGranted(EntityDir\User::ROLE_PA) && !in_array($user->getRoleName(), [EntityDir\User::ROLE_PA_ADMIN, EntityDir\User::ROLE_PA_TEAM_MEMBER])) {
                $user->setRoleName(EntityDir\User::ROLE_PA_TEAM_MEMBER);
            }

            if ($this->isGranted(EntityDir\User::ROLE_PROF) && !in_array($user->getRoleName(), [EntityDir\User::ROLE_PROF_ADMIN, EntityDir\User::ROLE_PROF_TEAM_MEMBER])) {
                $user->setRoleName(EntityDir\User::ROLE_PROF_TEAM_MEMBER);
            }

            try {
                $user = $this->getRestClient()->post('user', $user, ['pa_team_add'], 'User');

                $request->getSession()->getFlashBag()->add('notice', 'The user has been added');

                // activation link
                $activationEmail = $this->getMailFactory()->createActivationEmail($user);
                $this->getMailSender()->send($activationEmail, ['text', 'html']);

                return $this->redirectToRoute('pa_team');
            } catch (\Exception $e) {
                switch ((int) $e->getCode()) {
                    case 422:
                        $form->get('email')->addError(new FormError($this->get('translator')->trans('form.email.existingError', [], 'pa-team')));
                        break;

                    default:
                        throw $e;
                }
            }
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/edit/{id}", name="edit_team_member")
     * @Template()
     */
    public function editAction(Request $request, $id)
    {
        $user = $this->getRestClient()->get('team/member/' . $id, 'User');

        $this->denyAccessUnlessGranted('edit-user', $user, 'Access denied');

        if ($this->getUser()->getId() == $user->getId()) {
            throw $this->createNotFoundException('User cannot edit their own account at this URL');
        }

        $team = $this->getRestClient()->get('user/' . $this->getUser()->getId() . '/team', 'Team');
        $validationGroups = $team->canAddAdmin() ? ['user_details_pa', 'pa_team_role_name'] : ['user_details_pa'];

        $form = $this->createForm(FormDir\Pa\TeamMemberAccountType::class, $user, [ 'team'                => $team, 'loggedInUser'      => $this->getUser(), 'targetUser'        => $user, 'validation_groups' => $validationGroups
                                   ]
                                 );

        $form->handleRequest($request);

        if ($form->isValid()) {
            $user = $form->getData();

            try {
                $this->getRestClient()->put('user/' . $id, $user, ['pa_team_add'], 'User');

                if ($id == $this->getUser()->getId() && ($user->getRoles() != $this->getUser()->getRoles())) {
                    $request->getSession()->getFlashBag()->add('notice', 'For security reasons you have been logged out because you have changed your admin rights. Please log in again below');
                    $redirectRoute = 'logout';
                } else {
                    $request->getSession()->getFlashBag()->add('notice', 'The user has been edited');
                    $redirectRoute = 'pa_team';
                }

                return $this->redirectToRoute($redirectRoute);
            } catch (\Exception $e) {
                switch ((int) $e->getCode()) {
                    case 422:
                        $form->get('email')->addError(new FormError($this->get('translator')->trans('form.email.existingError', [], 'pa-team')));
                        break;

                    default:
                        throw $e;
                }
            }
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * Resend activation email to pa team member
     *
     * @Route("/send-activation-link/{id}", name="team_send_activation_link")
     *
     * @param Request $request
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function resendActivationEmailAction(Request $request, $id)
    {
        try {
            /* @var $user EntityDir\User */
            $user = $this->getRestClient()->get('team/member/' . $id, 'User');

            $user = $this->getRestClient()->userRecreateToken($user->getEmail(), 'pass-reset');
            $activationEmail = $this->getMailFactory()->createActivationEmail($user);
            $this->getMailSender()->send($activationEmail, ['text', 'html']);

            $request->getSession()->getFlashBag()->add(
                'notice',
                'An activation email has been sent to the user.'
            );
        } catch (\Exception $e) {
            $this->get('logger')->debug($e->getMessage());
            $request->getSession()->getFlashBag()->add(
                'error',
                'An activation email could not be sent.'
            );
        }

        return $this->redirectToRoute('pa_team');
    }

    /**
     * Confirm delete user form
     *
     * @Route("/delete-user/{id}", name="delete_team_member")
     * @Template()
     */
    public function deleteConfirmAction(Request $request, $id, $confirmed = false)
    {
        // The rest call ensures that only team members get returned and permission checks work as expected
        $user = $this->getRestClient()->get('team/member/' . $id, 'User');

        $this->denyAccessUnlessGranted('delete-user', $user, 'Access denied');

        return ['user' => $user];
    }

    /**
     * Removes a user, adds a flash message and redirects to page
     *
     * @Route("/delete-user/{id}/confirm", name="delete_team_member_confirm")
     * @Template()
     */
    public function deleteConfirmedAction(Request $request, $id)
    {
        try {
            $userToRemove = $this->getRestClient()->get('team/member/' . $id, 'User');

            $this->denyAccessUnlessGranted('delete-user', $userToRemove, 'Access denied');

            $this->getRestClient()->delete('/team/delete-user/' . $userToRemove->getId());

            $request->getSession()->getFlashBag()->add('notice', $userToRemove->getFullName() . ' has been removed');
        } catch (\Exception $e) {
            $this->get('logger')->debug($e->getMessage());

            if ($e instanceof RestClientException && isset($e->getData()['message'])) {
                $request->getSession()->getFlashBag()->add(
                    'error',
                    'User could not be removed'
                );
            }
        }

        return $this->redirectToRoute('pa_team');
    }
}
