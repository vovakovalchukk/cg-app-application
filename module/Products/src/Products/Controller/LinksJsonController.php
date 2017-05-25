<?php

namespace Products\Controller;

use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Mvc\Controller\AbstractActionController;
use CG_UI\View\Prototyper\JsonModelFactory;

use CG\Product\Link\Filter as ProductLinkFilter;
use CG\Product\Link\Service as ProductLinkService;
use CG\Product\Link\Mapper as ProductLinkMapper;

class LinksJsonController extends AbstractActionController
{
    const ROUTE_AJAX = 'Links AJAX';

    protected $jsonModelFactory;
    protected $productLinkService;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        ProductLinkService $productLinkService
    ) {
        $this->jsonModelFactory = $jsonModelFactory;
        $this->productLinkService = $productLinkService;
    }

    public function ajaxAction()
    {
        $products = json_decode($this->params()->fromPost('products'), true);

        $allVariationsBySkus = [];
        foreach ($products as $parentProductId => $variations) {
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
                    ];
                }
            }
        }

        return $this->jsonModelFactory->newInstance([
            'productLinks' => $productLinksByProductId
        ]);
    }
}
