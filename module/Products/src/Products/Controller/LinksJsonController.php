<?php

namespace Products\Controller;

use CG\ETag\Exception\NotModified;
use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Mvc\Controller\AbstractActionController;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG\User\ActiveUserInterface;

use CG\Product\Link\Service as ProductLinkService;
use CG\Product\Link\Mapper as ProductLinkMapper;

class LinksJsonController extends AbstractActionController
{
    const ROUTE_AJAX = 'Links AJAX';
    const ROUTE_SAVE = 'Links Save';
    const ROUTE_REMOVE = 'Links Remove';

    protected $jsonModelFactory;
    protected $activeUserContainer;
    protected $productLinkService;
    protected $productLinkMapper;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        ActiveUserInterface $activeUserContainer,
        ProductLinkService $productLinkService,
        ProductLinkMapper $productLinkMapper
    ) {
        $this->jsonModelFactory = $jsonModelFactory;
        $this->activeUserContainer = $activeUserContainer;
        $this->productLinkService = $productLinkService;
        $this->productLinkMapper = $productLinkMapper;
    }

    public function ajaxAction()
    {
        $ouId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        $productIds = json_decode($this->params()->fromPost('products'), true);

        $allVariationsBySkus = $this->productLinkMapper->getVariationsBySkus($productIds);

        if (! empty($allVariationsBySkus)) {
            $productLinks = $this->productLinkService->fetchLinksForSkus($ouId, array_keys($allVariationsBySkus));
        }

        $productLinksByProductId = [];
        if (! empty($productLinks)) {
            $productLinksByProductId = $this->productLinkService->getProductLinksByProductId($ouId, $productIds, $allVariationsBySkus, $productLinks);
        }

        return $this->jsonModelFactory->newInstance([
            'productLinks' => $productLinksByProductId
        ]);
    }

    public function saveAction()
    {
        $ou = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        $sku = $this->params()->fromPost('sku');
        $links = json_decode($this->params()->fromPost('links'), true);

        try {
            $productLink = $this->productLinkService->fetch($ou . '-' . $sku);
            $productLink->setStockSkuMap($this->productLinkMapper->convertToStockSkuMap($links));
        } catch (NotFound $e) {
            $productLink = $this->productLinkMapper->fromArray([
                'organisationUnitId' => $ou,
                'sku' => $sku,
                'stock' => $this->productLinkMapper->convertToStockSkuMap($links)
            ]);
        }
        $this->productLinkService->save($productLink);

        return $this->jsonModelFactory->newInstance([
            'success' => true
        ]);
    }

    public function removeAction()
    {
        $ou = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        $sku = $this->params()->fromPost('sku');

        try {
            $productLink = $this->productLinkService->fetch($ou . '-' . $sku);
            $this->productLinkService->remove($productLink);
        } catch (NotModified $e) {
            return $this->jsonModelFactory->newInstance([
                'error' => "The product link was not modified, please try again."
            ]);
        }

        return $this->jsonModelFactory->newInstance([
            'success' => true
        ]);
    }
}
