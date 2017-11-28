<?php

namespace Products\Controller;

use CG\ETag\Exception\NotModified;
use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Mvc\Controller\AbstractActionController;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG\User\ActiveUserInterface;
use Products\Product\Link\Service as ProductLinkService;
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
        $skusToFetchLinkedProductsFor = json_decode($this->params()->fromPost('skus'), true);

        if (! empty($skusToFetchLinkedProductsFor)) {
            $productLinks = $this->productLinkService->fetchLinksForSkus($ouId, array_keys($skusToFetchLinkedProductsFor));
        }

        $productLinksByProductId = [];
        if (count($productLinks) > 0) {
            $productLinksByProductId = $this->productLinkService->getProductLinksByProductId($ouId, $skusToFetchLinkedProductsFor, $productLinks);
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
        $productLinkExists = true;

        try {
            $productLink = $this->productLinkService->fetch($ou . '-' . $sku);
            $productLink->setStockSkuMap($this->productLinkMapper->convertToStockSkuMap($links));
        } catch (NotFound $e) {
            $productLink = $this->productLinkMapper->fromArray([
                'organisationUnitId' => $ou,
                'sku' => $sku,
                'stock' => $this->productLinkMapper->convertToStockSkuMap($links)
            ]);
            $productLinkExists = false;
        }

        if ($productLinkExists && $productLink->getStockSkuMap() == []) {
            $this->productLinkService->remove($productLink);
        }

        if (count($productLink->getStockSkuMap()) > 0) {
            $this->productLinkService->save($productLink);
        }

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
