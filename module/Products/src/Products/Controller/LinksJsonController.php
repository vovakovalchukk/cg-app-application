<?php

namespace Products\Controller;

use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Mvc\Controller\AbstractActionController;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG\User\ActiveUserInterface;

use CG\Product\Link\Filter as ProductLinkFilter;
use CG\Product\Link\Service as ProductLinkService;
use CG\Product\Link\Mapper as ProductLinkMapper;
use CG\Product\Filter as ProductFilter;
use CG\Product\Service\Service as ProductService;
use CG\Product\Mapper as ProductMapper;

class LinksJsonController extends AbstractActionController
{
    const ROUTE_AJAX = 'Links AJAX';
    const ROUTE_SAVE = 'Links Save';

    protected $jsonModelFactory;
    protected $activeUserContainer;
    protected $productLinkService;
    protected $productService;
    protected $productMapper;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        ActiveUserInterface $activeUserContainer,
        ProductLinkService $productLinkService,
        ProductService $productService,
        ProductMapper $productMapper
    ) {
        $this->jsonModelFactory = $jsonModelFactory;
        $this->activeUserContainer = $activeUserContainer;
        $this->productLinkService = $productLinkService;
        $this->productService = $productService;
        $this->productMapper = $productMapper;
    }

    public function ajaxAction()
    {
        $productIds = json_decode($this->params()->fromPost('products'), true);

        $allVariationsBySkus = [];
        foreach ($productIds as $parentProductId => $variations) {
            foreach ($variations as $variation) {
                $allVariationsBySkus[$variation['sku']] = $variation;
            }
        }

        try {
            $filter = (new ProductLinkFilter('all', 1))
                ->setProductSku(array_keys($allVariationsBySkus));
            $productLinks = $this->productLinkService->fetchCollectionByFilter($filter);
        } catch(NotFound $e) {
            $productLinks = [];
        }

        try {
            $ouId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
            $products = $this->productService->fetchCollectionByOUAndId([$ouId], array_keys($productIds));
        } catch(NotFound $e) {
            $products = [];
        }

        $productLinksByProductId = [];
        foreach ($allVariationsBySkus as $sku => $variation) {
            $linkedProduct = $productLinks->getById($variation['organisationUnitId'].'-'.$sku);
            if ($linkedProduct) {
                foreach ($linkedProduct->getStockSkuMap() as $stockSku => $stockQty) {
                    $imageUrl = "";
                    if (isset($allVariationsBySkus[$stockSku]) && isset($allVariationsBySkus[$stockSku]['images'][0])) {
                        $imageUrl = $allVariationsBySkus[$stockSku]['images'][0]['url'];
                    }
                    $product = $products->getById($variation['parentProductId']);
                    $productLinksByProductId[$variation['parentProductId']][$variation['id']][] = [
                        'sku' => $stockSku,
                        'quantity' => $stockQty,
                        'imageUrl' => $imageUrl,
                        'product' => $this->productMapper->getFullProductDataArray($product)
                    ];
                }
            }
        }

        return $this->jsonModelFactory->newInstance([
            'productLinks' => $productLinksByProductId
        ]);
    }

    public function saveAction()
    {
        $sku = $this->params()->fromPost('sku');
        $links = json_decode($this->params()->fromPost('links'), true);

        return $this->jsonModelFactory->newInstance([
            'success' => true
        ]);
    }
}
