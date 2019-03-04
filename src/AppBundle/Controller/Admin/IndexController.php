<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Controller\AbstractController;
use AppBundle\DTO\ClientDto;
use AppBundle\DTO\DeputyClientsDto;
use AppBundle\DTO\DeputyDto;
use AppBundle\Entity as EntityDir;
use AppBundle\Entity\Client;
use AppBundle\Exception\DisplayableException;
use AppBundle\Exception\RestClientException;
use AppBundle\Form as FormDir;
use AppBundle\Model\Email;
use AppBundle\Service\CsvUploader;
use AppBundle\Service\DataImporter\CsvToArray;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin")
 */
class IndexController extends AbstractController
{
    /**
     * @Route("/", name="admin_homepage")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template
     */
    public function indexAction(Request $request)
    {
        $filters = [
            'limit'       => 100,
            'offset'      => $request->get('offset', 'id'),
            'role_name'   => '',
            'q'           => '',
            'ndr_enabled' => '',
            'order_by'    => 'registrationDate',
            'sort_order'  => 'DESC',
        ];

        $form = $this->createForm(FormDir\Admin\SearchType::class, null, ['method' => 'GET']);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $filters = $form->getData() + $filters;
        }

        $users = $this->getRestClient()->get('user/get-all?' . http_build_query($filters), 'User[]');

        return [
            'form'    => $form->createView(),
            'users'   => $users,
            'filters' => $filters,
        ];
    }

    /**
     * @Route("/user-add", name="admin_add_user")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template
     */
    public function addUserAction(Request $request)
    {
        $availableRoles = [
            EntityDir\User::ROLE_LAY_DEPUTY => 'Lay Deputy',
            EntityDir\User::ROLE_AD         => 'Assisted Digital',
        ];
        // only admins can add other admins
        if ($this->isGranted(EntityDir\User::ROLE_ADMIN)) {
            $availableRoles[EntityDir\User::ROLE_ADMIN] = 'OPG Admin';
            $availableRoles[EntityDir\User::ROLE_CASE_MANAGER] = 'Case manager';
        }

        $form = $this->createForm(FormDir\Admin\AddUserType::class,
            new EntityDir\User(), [
                'options' => [
                    'roleChoices' => $availableRoles,
                    'roleNameEmptyValue' => $this->get('translator')->trans('addUserForm.roleName.defaultOption', [], 'admin')
                ]
            ]
        );

        $form->handleRequest($request);
        if ($form->isValid()) {
            // add user
            try {
                if (!$this->isGranted(EntityDir\User::ROLE_ADMIN) && $form->getData()->getRoleName() == EntityDir\User::ROLE_ADMIN) {
                    throw new \RuntimeException('Cannot add admin from non-admin user');
                }
                $user = $this->getRestClient()->post('user', $form->getData(), ['admin_add_user'], 'User');

                $activationEmail = $this->getMailFactory()->createActivationEmail($user);
                $this->getMailSender()->send($activationEmail, ['text', 'html']);

                $request->getSession()->getFlashBag()->add(
                    'notice',
                    'An activation email has been sent to the user.'
                );

                return $this->redirect($this->generateUrl('admin_homepage'));
            } catch (RestClientException $e) {
                $form->get('email')->addError(new FormError($e->getData()['message']));
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/edit-user", name="admin_editUser")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Method({"GET", "POST"})
     * @Template
     *
     * @param Request $request
     */
    public function editUserAction(Request $request)
    {
        $what = $request->get('what');
        $filter = $request->get('filter');

        try {
            /* @var $user EntityDir\User */
            // Fetch me a DeputyDto
            // This will be a serialized object containing Deputy, Client information.
            // Perhaps a DeputyClientsDto - this would be a composite of a DeputyDto and a ClientDto
            // A ClientDto also gathers Report info, but not to the extent of a ReportDto

            // Fields required:
            // D.roleName, D.id, D.firstname, D.lastname, D.addresspostcode, D.email, D.ndrenabled, D.clients:
            //  C.ndr.id, C.firstname, C.lastname, C.casenumber, C.reports (ids)



            $user = $this->getRestClient()->get("user/get-one-by/{$what}/{$filter}", 'array', ['user', 'user-clients', 'client', 'client-reports', 'ndr']);
            // At this point, user is an array which needs assembling into a DeputyClientsDto

            $deputy = new EntityDir\User();
            $deputy
                ->setId($user['deputy']['id'])
                ->setFirstname($user['deputy']['firstname'])
                ->setLastname($user['deputy']['lastname'])
                ->setEmail($user['deputy']['email'])
                ->setRoleName($user['deputy']['roleName'])
                ->setAddressPostcode($user['deputy']['postcode'])
                ->setNdrEnabled($user['deputy']['ndrEnabled']);

            $clients = [];
            foreach ($user['clients'] as $clientArray) {
                $client = new Client();

                $ndr = null;
                if (null !== $clientArray['ndrId']) {
                    $ndr = new EntityDir\Ndr\Ndr();
                    $ndr->setId($clientArray['ndrId']);
                }

                $client
                    ->setId($clientArray['id'])
                    ->setCaseNumber($clientArray['caseNumber'])
                    ->setFirstname($clientArray['firstname'])
                    ->setLastname($clientArray['lastname'])
                    ->setEmail($clientArray['email'])
                    ->setNdr($ndr)
                    ->setTotalReportCount($clientArray['reportCount']);
                $clients[] = $client;
            }
            $deputy->setClients($clients);


        } catch (\Exception $e) {
            return $this->render('AppBundle:Admin:error.html.twig', [
                'error' => 'User not found',
            ]);
        }

        if ($deputy->getRoleName() == EntityDir\User::ROLE_ADMIN && !$this->isGranted(EntityDir\User::ROLE_ADMIN)) {
            return $this->render('AppBundle:Admin:error.html.twig', [
                'error' => 'Non-admin cannot edit admin users',
            ]);
        }

        // no role editing for current user and PA
        $roleNameSetTo = null;
        if ($deputy->getId() == $this->getUser()->getId() || $deputy->getRoleName() == EntityDir\User::ROLE_PA_NAMED) {
            $roleNameSetTo = $deputy->getRoleName();
        }
        $form = $this->createForm(FormDir\Admin\AddUserType::class, $deputy, ['options' => [
            'roleChoices'        => [
                EntityDir\User::ROLE_ADMIN      => 'OPG Admin',
                EntityDir\User::ROLE_CASE_MANAGER   => 'Case manager',
                EntityDir\User::ROLE_LAY_DEPUTY => 'Lay Deputy',
                EntityDir\User::ROLE_AD         => 'Assisted Digital',
                EntityDir\User::ROLE_PA_NAMED   => 'Public Authority (named)',
                EntityDir\User::ROLE_PROF_NAMED => 'Professional Deputy (named)',
            ],
            'roleNameEmptyValue' => $this->get('translator')->trans('addUserForm.roleName.defaultOption', [], 'admin'),
            'roleNameSetTo'      => $roleNameSetTo, //can't edit current user's role
            'ndrEnabledType'     => $deputy->getRoleName() == EntityDir\User::ROLE_LAY_DEPUTY ? 'checkbox' : 'hidden',
        ]]);

        $clients = $deputy->getClients();
        $ndr = null;
        $ndrForm = null;
        if (count($clients)) {
            $ndr = $clients[0]->getNdr();
            if ($ndr) {
                $ndrForm = $this->createForm(FormDir\NdrType::class, $ndr, [
                    'action' => $this->generateUrl('admin_editNdr', ['id' => $ndr->getId()]),
                ]);
            }
        }

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $updateUser = $form->getData();


                try {
                    $this->getRestClient()->put('user/' . $deputy->getId(), $updateUser, ['admin_add_user']);

                    $request->getSession()->getFlashBag()->add('notice', 'Your changes were saved');

                    $this->redirect($this->generateUrl('admin_editUser', ['what' => 'user_id', 'filter' => $deputy->getId()]));
                } catch (\Exception $e) {
                    switch ((int) $e->getCode()) {
                        case 422:
                            $form->get('email')->addError(new FormError($this->get('translator')->trans('editUserForm.email.existingError', [], 'admin')));
                            break;

                        default:
                            throw $e;
                    }
                }
            }
        }
        $view = [
            'form'          => $form->createView(),
            'action'        => 'edit',
            'id'            => $deputy->getId(),
            'user'          => $deputy,
            'deputyBaseUrl' => $this->container->getParameter('non_admin_host'),
        ];

        if ($ndr && $ndrForm) {
            $view['ndrForm'] = $ndrForm->createView();
        }

        return $view;
    }

    /**
     * @Route("/edit-ndr/{id}", name="admin_editNdr")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Method({"POST"})
     *
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editNdrAction(Request $request, $id)
    {
        $ndr = $this->getRestClient()->get('ndr/' . $id, 'Ndr\Ndr', ['ndr', 'client', 'client-users', 'user']);
        $ndrForm = $this->createForm(FormDir\NdrType::class, $ndr);
        if ($request->getMethod() == 'POST') {
            $ndrForm->handleRequest($request);

            if ($ndrForm->isValid()) {
                $updateNdr = $ndrForm->getData();
                $this->getRestClient()->put('ndr/' . $id, $updateNdr, ['start_date']);
                $request->getSession()->getFlashBag()->add('notice', 'Your changes were saved');
            }
        }
        /** @var EntityDir\Client $client */
        $client = $ndr->getClient();
        $users = $client->getUsers();

        return $this->redirect($this->generateUrl('admin_editUser', ['what' => 'user_id', 'filter' => $users[0]->getId()]));
    }

    /**
     * @Route("/delete-confirm/{id}", name="admin_delete_confirm")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Method({"GET"})
     * @Template()
     *
     * @param int $id
     *
     * @return array
     */
    public function deleteConfirmAction($id)
    {
        $userToDelete = $this->getRestClient()->get("user/{$id}", 'User');

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            throw new DisplayableException('Only Admin can delete users');
        }

        if ($this->getUser()->getId() == $userToDelete->getId()) {
            throw new DisplayableException('Cannot delete logged user');
        }

        return ['user' => $userToDelete];
    }

    /**
     * @Route("/delete/{id}", name="admin_delete")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Method({"GET"})
     * @Template()
     *
     * @param int $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($id)
    {
        $user = $this->getRestClient()->get("user/{$id}", 'User', ['user', 'client', 'client-reports', 'report']);

        $this->getRestClient()->delete('user/' . $id);

        return $this->redirect($this->generateUrl('admin_homepage'));
    }

    /**
     * @Route("/casrec-upload", name="casrec_upload")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template
     */
    public function uploadUsersAction(Request $request)
    {
        $chunkSize = 2000;

        $form = $this->createForm(FormDir\UploadCsvType::class, null, [
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $fileName = $form->get('file')->getData();
            try {
                $csvToArray = new CsvToArray($fileName, false, true);

                $data = $csvToArray->setExpectedColumns([
                        'Case',
                        'Surname',
                        'Deputy No',
                        'Dep Surname',
                        'Dep Postcode',
                        'Typeofrep',
                        'Corref',
                        'NDR', // if not present, would indicate a prof/PA CSV is being used incorrectly here
                        'Dep Type',
                        'Dep Adrs1',
                        'Dep Adrs2',
                        'Dep Adrs3'
                    ])
                    ->setOptionalColumns($csvToArray->getFirstRow())
                    ->setUnexpectedColumns([
                        //'Pat Create', 'Dship Create', //should hold reg date / Cour order date, but no specs given yet
                        'Last Report Day'
                    ])
                    ->getData();

                // small amount of data -> immediate posting and redirect (needed for behat)
                if (count($data) < $chunkSize) {
                    $compressedData = CsvUploader::compressData($data);

                    $this->getRestClient()->delete('casrec/truncate');
                    $ret = $this->getRestClient()->setTimeout(600)->post('casrec/bulk-add', $compressedData);
                    $request->getSession()->getFlashBag()->add(
                        'notice',
                        sprintf('%d record uploaded, %d error(s)', $ret['added'], count($ret['errors']))
                    );

                    foreach ($ret['errors'] as $err) {
                        $request->getSession()->getFlashBag()->add(
                            'error',
                            $err
                        );
                    }

                    return $this->redirect($this->generateUrl('casrec_upload'));
                }

                // big amount of data => store in redis + redirect
                $chunks = array_chunk($data, $chunkSize);
                foreach ($chunks as $k => $chunk) {
                    $compressedData = CsvUploader::compressData($chunk);
                    $this->get('snc_redis.default')->set('chunk' . $k, $compressedData);
                }


                return $this->redirect($this->generateUrl('casrec_upload', ['nOfChunks' => count($chunks)]));
            } catch (\Exception $e) {
                $message = $e->getMessage();
                if ($e instanceof RestClientException && isset($e->getData()['message'])) {
                    $message = $e->getData()['message'];
                }
                $form->get('file')->addError(new FormError($message));
            }
        }

        return [
            'nOfChunks'      => $request->get('nOfChunks'),
            'currentRecords' => $this->getRestClient()->get('casrec/count', 'array'),
            'form'           => $form->createView(),
            'maxUploadSize'  => min([ini_get('upload_max_filesize'), ini_get('post_max_size')]),
        ];
    }

    /**
     * @Route("/casrec-mld-upgrade", name="casrec_mld_upgrade")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template
     */
    public function upgradeMldAction(Request $request)
    {
        $form = $this->createForm(FormDir\UploadCsvType::class, null, [
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $fileName = $form->get('file')->getData();
            try {
                $data = (new CsvToArray($fileName, true))
                    ->setExpectedColumns([
                        'Deputy No'
                    ])
                    ->getData();
                $compressedData = CsvUploader::compressData($data);
                $ret = $this->getRestClient()->setTimeout(600)->post('codeputy/mldupgrade', $compressedData);
                $request->getSession()->getFlashBag()->add(
                    'notice',
                    sprintf('Your file contained %d deputy numbers, %d were updated, with %d error(s)', $ret['requested_mld_upgrades'], $ret['updated'], count($ret['errors']))
                );

                foreach ($ret['errors'] as $err) {
                    $request->getSession()->getFlashBag()->add(
                        'error',
                        $err
                    );
                }
                return $this->redirect($this->generateUrl('casrec_mld_upgrade'));
            } catch (\Exception $e) {
                $message = $e->getMessage();
                if ($e instanceof RestClientException && isset($e->getData()['message'])) {
                    $message = $e->getData()['message'];
                }
                $form->get('file')->addError(new FormError($message));
            }
        }

        return [
            'currentMldUsers' => $this->getRestClient()->get('codeputy/count', 'array'),
            'form'            => $form->createView(),
            'maxUploadSize'   => min([ini_get('upload_max_filesize'), ini_get('post_max_size')]),
        ];
    }

    /**
     * @Route("/org-csv-upload", name="admin_org_upload")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template
     */
    public function uploadOrgUsersAction(Request $request)
    {
        $chunkSize = 100;

        $form = $this->createForm(FormDir\UploadCsvType::class, null, [
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $fileName = $form->get('file')->getData();
            try {
                $data = (new CsvToArray($fileName, false))
                    ->setExpectedColumns([
                        'Deputy No',
                        //'Pat Create', 'Dship Create', //should hold reg date / Cour order date, but no specs given yet
                        'Dep Postcode',
                        'Dep Forename',
                        'Dep Surname',
                        'Dep Type', // 23 = PA (but not confirmed)
                        'Dep Adrs1',
                        'Dep Adrs2',
                        'Dep Adrs3',
                        'Dep Postcode',
                        'Email', //mandatory, used as user ID whem uploading
                        'Case', //case number, used as ID when uploading
                        'Forename', 'Surname', //client forename and surname
                        'Corref',
                        'Typeofrep',
                        'Last Report Day',
                    ])
                    ->setOptionalColumns([
                        'Client Adrs1',
                        'Client Adrs2',
                        'Client Adrs3',
                        'Client Postcode',
                        'Client Phone',
                        'Client Email',
                        'Client Date of Birth',
                    ])
                    ->setUnexpectedColumns([
                        'NDR'
                    ])
                    ->getData();

                // small chunk => upload in same request
                if (count($data) < $chunkSize) {
                    $compressedData = CsvUploader::compressData($data);
                    $this->get('org_service')->uploadAndSetFlashMessages($compressedData, $request->getSession()->getFlashBag());
                    return $this->redirect($this->generateUrl('admin_org_upload'));
                }

                // big amount of data => save data into redis and redirect with nOfChunks param so that JS can do the upload with small AJAX calls
                $chunks = array_chunk($data, $chunkSize);

                foreach ($chunks as $k => $chunk) {

                    $compressedData = CsvUploader::compressData($chunk);
                    $this->get('snc_redis.default')->set('org_chunk' . $k, $compressedData);
                }
                return $this->redirect($this->generateUrl('admin_org_upload', ['nOfChunks' => count($chunks)]));
            } catch (\Exception $e) {
                $message = $e->getMessage();
                if ($e instanceof RestClientException && isset($e->getData()['message'])) {
                    $message = $e->getData()['message'];
                }
                $form->get('file')->addError(new FormError($message));
            }
        }

        return [
            'nOfChunks'      => $request->get('nOfChunks'),
            'form'          => $form->createView(),
            'maxUploadSize' => min([ini_get('upload_max_filesize'), ini_get('post_max_size')]),
        ];
    }

    /**
     * @Route("/send-activation-link/{email}", name="admin_send_activation_link")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     **/
    public function sendUserActivationLinkAction(Request $request, $email)
    {
        try {
            /* @var $user EntityDir\User */
            $user = $this->getRestClient()->userRecreateToken($email, 'pass-reset');
            $resetPasswordEmail = $this->getMailFactory()->createActivationEmail($user);

            $this->getMailSender()->send($resetPasswordEmail, ['text', 'html']);
        } catch (\Exception $e) {
            $this->get('logger')->debug($e->getMessage());
        }

        return new Response('[Link sent]');
    }
}
