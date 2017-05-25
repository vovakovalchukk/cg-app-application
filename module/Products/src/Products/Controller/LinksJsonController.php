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

class LinksJsonController extends AbstractActionController
{
    const ROUTE_AJAX = 'Links AJAX';

    protected $jsonModelFactory;
    protected $activeUserContainer;
    protected $productLinkService;
    protected $productService;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        ActiveUserInterface $activeUserContainer,
        ProductLinkService $productLinkService,
        ProductService $productService
    ) {
        $this->jsonModelFactory = $jsonModelFactory;
        $this->activeUserContainer = $activeUserContainer;
        $this->productLinkService = $productLinkService;
        $this->productService = $productService;
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
                    $productLinksByProductId[$variation['parentProductId']][$variation['id']][] = [
                        'sku' => $stockSku,
                        'quantity' => $stockQty,
                        'imageUrl' => $imageUrl,
                        'product' => $products->getById($variation['parentProductId'])->toArray()
                    ];
                }
            }
        }

        return $this->jsonModelFactory->newInstance([
            'productLinks' => $productLinksByProductId
        ]);
    }
}
