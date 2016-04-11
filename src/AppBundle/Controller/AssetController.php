<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Entity\Report;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @Route("/report")
 */
class AssetController extends AbstractController
{
    /**
     * List assets and also handle no-asset checkbox-form
     *
     * @Route("/{reportId}/assets", name="assets")
     * @Template("AppBundle:Asset:list.html.twig")
     */
    public function listAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['transactions', 'basic', 'client', 'asset', 'accounts']);
        $assets = $report->getAssets();
       
        // if there are no assets and the report is not due, show new asset form
        if (empty($assets) && !$report->isDue()) {
            return $this->redirect($this->generateUrl('asset_add_select_title', [ 'reportId' => $reportId]));
        }

        return [
            'report' => $report,
            'assets' => $assets
        ];
    }

    /**
     * Form to select asset title (dropdown only)
     * when submitted and valid, redirects to 'asset_add_complete'.
     *
     * When JS is enabled, there the content of that page is auto-loaded via AJAX
     *
     * @Route("/{reportId}/assets/add", name="asset_add_select_title")
     * @Template("AppBundle:Asset:addSelectTitle.html.twig")
     */
    public function addSelectTitleAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['transactions', 'basic', 'client', 'accounts']);

        $form = $this->createForm('asset_title', new EntityDir\AssetOther(), [
            'action' => $this->generateUrl('asset_add_select_title', [ 'reportId' => $reportId])
        ]);

        $form->handleRequest($request);
        if ($form->isValid()) {
            return $this->redirect($this->generateUrl('asset_add_complete', [ 'reportId' => $reportId, 'title' => $form->getData()->getTitle()]));
        } 

        return [
            'report' => $report,
            'form' => $form->createView(),
            'showCancelLink' => count($report->getAssets()) > 0,
        ];
    }

    /**
     * Shows the full add asset form
     *
     * @Route("/{reportId}/assets/add-complete/{title}", name="asset_add_complete")
     * @Template("AppBundle:Asset:addComplete.html.twig")
     */
    public function addCompleteAction(Request $request, $reportId, $title)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['transactions', 'basic', 'client', 'accounts']);

        // [.. change form and template (or forward) depending on the asset title ]
        $asset = EntityDir\Asset::factory($title);
        
        $form = $this->createForm( FormDir\Asset\AbstractAssetType::factory($title), $asset, [
            'action' => $this->generateUrl('asset_add_complete', [ 'reportId' => $reportId, 'title' => $title])
        ]);

        $form->handleRequest($request);

        // handle submit report
        if ($form->isValid()) {
            $asset = $form->getData();
            $asset->setReport($report);
            $this->get('restClient')->post("report/{$reportId}/asset", $asset);
            $report->setNoAssetToAdd(false);
            $this->getRestClient()->put('report/' . $reportId, $report);
            $this->flashSuccess();
            
            return $this->redirect($this->generateUrl('assets', [ 'reportId' => $reportId]));
        } else if ($form->isSubmitted()) {
            $this->flashFailure();
        }

        return [
            'report' => $report,
            'form' => $form->createView(),
            'asset' => $asset
        ];
    }

    /**
     * Edit a record
     * the edit form is "inline" so it needs
     *
     * @Route("/{reportId}/assets/{assetId}/edit", name="asset_edit")
     * @Template("AppBundle:Asset:edit.html.twig")
     */
    public function editAction(Request $request, $reportId, $assetId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['transactions', 'basic', 'client', 'asset', 'accounts']);
        if (!$report->hasAssetWithId($assetId)) {
            throw new \RuntimeException("Asset not found.");
        }
        $asset = $this->get('restClient')->get("report/{$reportId}/asset/{$assetId}", 'Asset');
        $form = $this->createForm(FormDir\Asset\AbstractAssetType::factory($asset->getType()), $asset);

        $form->handleRequest($request);

        // handle submit report
        if ($form->isValid()) {
            $asset = $form->getData();
            $this->get('restClient')->put("report/{$reportId}/asset/{$assetId}", $asset);
            $this->flashSuccess();
            
            return $this->redirect($this->generateUrl('assets', [ 'reportId' => $reportId]));
        } else if ($form->isSubmitted()) {
            $this->flashFailure();
        }

        return [
            'report' => $report,
            'assetToEdit' => $asset,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/{reportId}/assets/{id}/delete", name="delete_asset")
     * @param integer $id
     *
     * @return RedirectResponse
     */
    public function deleteAction($reportId, $id)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['transactions', 'basic', 'client', 'asset', 'accounts']);
        $restClient = $this->getRestClient(); /* @var $restClient RestClient */

        if ($report->hasAssetWithId($id)) {
            $restClient->delete("/report/{$reportId}/asset/{$id}");
        }
        $this->flashSuccess();

        return $this->redirect($this->generateUrl('assets', [ 'reportId' => $reportId]));
    }

    /**
     * Sub controller action called when the no decision form is embedded in another page.
     * @Route("/{reportId}/noassets", name="no_assets")
     *
     * @Template("AppBundle:Asset:_noAssets.html.twig")
     */
    public function _noAssetsAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['transactions', 'basic', 'client', 'asset', 'accounts']);
        $form = $this->createForm(new FormDir\NoAssetToAddType(), $report, []);
        $form->handleRequest($request);

        if ($request->getMethod() == "POST") {
            if ($form->isValid()) {
                $data = $form->getData();
                $this->getRestClient()->put('report/' . $reportId, $data, [
                     'deserialise_group' => 'no_assets_add'
                ]);
                $this->flashSuccess();
            } else {
                $this->flashFailure();
            }
        }

        return [
            'form' => $form->createView(),
            'report' => $report
        ];
    }

}
