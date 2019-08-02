<?php

namespace AppBundle\Controller\Admin\Organisations;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
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
 * @Route("/admin/organisations")
 */
class OrganisationsController extends AbstractController
{
    /**
     * @Route("/", name="admin_organisations_index")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template("AppBundle:Admin/Organisations/Client:details.html.twig")
     */
    public function indexAction(Request $request)
    {
        $filters = [
            'limit' => 100,
            'offset' => $request->get('offset', 'id'),
            'q' => '',
            'order_by' => 'organisationName',
            'sort_order' => 'ASC',
        ];

//        $form = $this->createForm(FormDir\Admin\Organisation\SearchType::class, null, ['method' => 'GET']);
//        $form->handleRequest($request);
//        if ($form->isValid()) {
//            $filters = $form->getData() + $filters;
//        }

        $organisations = $this->getRestClient()->get('v2/organisation/get-all?' . http_build_query($filters), 'Organisation[]');

        return [
//            'form' => $form->createView(),
            'organisations' => $organisations,
            'filters' => $filters,
        ];
    }
}
