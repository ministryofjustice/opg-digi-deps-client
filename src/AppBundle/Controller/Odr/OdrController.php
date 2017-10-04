<?php

namespace AppBundle\Controller\Odr;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\User;
use AppBundle\Form\Odr\ReportDeclarationType;
use AppBundle\Service\OdrStatusService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OdrController extends AbstractController
{
    private static $odrGroupsForValidation = [
        'user',
        'user-clients',
        'client',
        'client-reports',
        'odr',
        'report',
        'visits-care',
        'odr-account',
        'odr-debt',
        'odr-asset',
        'state-benefits',
        'pension',
        'damages',
        'one-off',
        'odr-expenses',
        'odr-action-give-gifts',
        'odr-action-property',
        'odr-action-more-info',
    ];

    /**
     * //TODO move view into Odr directory when branches are integrated.
     *
     * @Route("/odr", name="odr_index")
     * @Template()
     */
    public function indexAction()
    {
        // redirect if user has missing details or is on wrong page
        $user = $this->getUserWithData(array_merge(self::$odrGroupsForValidation, ['status']));
        if ($route = $this->get('redirector_service')->getCorrectRouteIfDifferent($user, 'odr_index')) {
            return $this->redirectToRoute($route);
        }

        $clients = $user->getClients();
        $client = !empty($clients) ? $clients[0] : null;
        $coDeputies = !empty($client) ? $client->getUsers() : [];
        $odr = $client->getOdr();

        $reports = $client ? $client->getReports() : [];
        arsort($reports);

        $reportActive = null;
        $reportsSubmitted = [];
        foreach ($reports as $currentReport) {
            if ($currentReport->getSubmitted()) {
                $reportsSubmitted[] = $currentReport;
            } else {
                $reportActive = $currentReport;
            }
        }

        $odrStatus = new OdrStatusService($odr);

        return [
            'client' => $client,
            'coDeputies' => $coDeputies,
            'odr' => $odr,
            'reportsSubmitted' => $reportsSubmitted,
            'reportActive' => $reportActive,
            'odrStatus' => $odrStatus
        ];
    }

    /**
     * @Route("/odr/{odrId}/overview", name="odr_overview")
     * @Template()
     */
    public function overviewAction($odrId)
    {
        // redirect if user has missing details or is on wrong page
        $user = $this->getUserWithData();
        if ($route = $this->get('redirector_service')->getCorrectRouteIfDifferent($user, 'odr_overview')) {
            return $this->redirectToRoute($route);
        }

        $client = $this->getFirstClient(self::$odrGroupsForValidation);
        $odr = $client->getOdr();
        if ($odr->getSubmitted()) {
            throw new \RuntimeException('Report already submitted and not editable.');
        }
        $odrStatus = new OdrStatusService($odr);

        return [
            'client' => $client,
            'odr' => $odr,
            'odrStatus' => $odrStatus,
        ];
    }

    /**
     * Used for active and archived ODRs.
     *
     * @Route("/odr/{odrId}/review", name="odr_review")
     * @Template()
     */
    public function reviewAction($odrId)
    {
        $client = $this->getFirstClient(self::$odrGroupsForValidation);
        $odr = $client->getOdr();
        $odr->setClient($client);

        // check status
        $odrStatusService = new OdrStatusService($odr);

        return [
            'odr' => $odr,
            'deputy' => $this->getUser(),
            'odrStatus' => $odrStatusService,
        ];
    }

    /**
     * @Route("/odr/{odrId}/deputyodr.pdf", name="odr_pdf")
     */
    public function pdfViewAction($odrId)
    {
        $client = $this->getFirstClient(self::$odrGroupsForValidation);
        $odr = $client->getOdr();
        $odr->setClient($client);

        $pdfBinary = $this->getPdfBinaryContent($odr);

        $response = new Response($pdfBinary);
        $response->headers->set('Content-Type', 'application/pdf');

        $attachmentName = sprintf('DigiOdr-%s_%s.pdf',
            $odr->getSubmitDate() ? $odr->getSubmitDate()->format('Y-m-d') : 'n-a-',
            $odr->getClient()->getCaseNumber()
        );

        $response->headers->set('Content-Disposition', 'attachment; filename="' . $attachmentName . '"');

        // Send headers before outputting anything
        $response->sendHeaders();

        return $response;
    }

    private function getPdfBinaryContent($odr)
    {
        $html = $this->render('AppBundle:Odr/Formatted:formatted_body.html.twig', [
            'odr' => $odr, 'adLoggedAsDeputy' => $this->isGranted(User::ROLE_AD)
        ])->getContent();

        return $this->get('wkhtmltopdf')->getPdfFromHtml($html);
    }

    /**
     * @Route("/odr/{odrId}/declaration", name="odr_declaration")
     * @Template()
     */
    public function declarationAction(Request $request, $odrId)
    {
        $client = $this->getFirstClient(self::$odrGroupsForValidation);
        $odr = $client->getOdr();
        $odr->setClient($client);

        // check status
        $odrStatus = new OdrStatusService($odr);
        if (!$odrStatus->isReadyToSubmit()) {
            throw new \RuntimeException('Report not ready for submission');
        }
        if ($odr->getSubmitted()) {
            throw new \RuntimeException('Report already submitted and not editable.');
        }

        $user = $this->getUserWithData(['user-clients', 'client']);
        $clients = $user->getClients();
        $client = $clients[0];

        $form = $this->createForm(new ReportDeclarationType(), $odr);
        $form->handleRequest($request);
        if ($form->isValid()) {
            // set report submitted with date
            $odr->setSubmitted(true)->setSubmitDate(new \DateTime());
            $this->getRestClient()->put('odr/' . $odr->getId() . '/submit', $odr, ['submit']);

            $pdfBinaryContent = $this->getPdfBinaryContent($odr);
            $reportEmail = $this->getMailFactory()->createOdrEmail($this->getUser(), $odr, $pdfBinaryContent);
            $this->getMailSender()->send($reportEmail, ['html'], 'secure-smtp');

            //send confirmation email
            $reportConfirmEmail = $this->getMailFactory()->createOdrSubmissionConfirmationEmail($this->getUser(), $odr);
            $this->getMailSender()->send($reportConfirmEmail, ['text', 'html']);

            return $this->redirect($this->generateUrl('odr_submit_confirmation', ['odrId'=>$odr->getId()]));
        }

        return [
            'odr' => $odr,
            'client' => $client,
            'form' => $form->createView(),
        ];
    }

    /**
     * Page displaying the report has been submitted.
     *
     * @Route("/odr/{odrId}/submitted", name="odr_submit_confirmation")
     * @Template()
     */
    public function submitConfirmationAction(Request $request, $odrId)
    {
        $client = $this->getFirstClient(self::$odrGroupsForValidation);
        $odr = $client->getOdr();
        if ($odr->getId() != $odrId) {
            throw new \RuntimeException('Not authorised to access this Report');
        }
        $odr->setClient($client);

        if (!$odr->getSubmitted()) {
            throw new \RuntimeException('Report not submitted');
        }

        $odrStatus = new OdrStatusService($odr);

        return [
            'odr' => $odr,
            'odrStatus' => $odrStatus,
        ];
    }
}
