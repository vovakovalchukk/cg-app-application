<?php

namespace Products\Controller;

use CG\ETag\Exception\NotModified;
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
    const ROUTE_REMOVE = 'Links Remove';

    protected $jsonModelFactory;
    protected $activeUserContainer;
    protected $productLinkService;
    protected $productLinkMapper;
    protected $productService;
    protected $productMapper;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        ActiveUserInterface $activeUserContainer,
        ProductLinkService $productLinkService,
        ProductLinkMapper $productLinkMapper,
        ProductService $productService,
        ProductMapper $productMapper
    ) {
        $this->jsonModelFactory = $jsonModelFactory;
        $this->activeUserContainer = $activeUserContainer;
        $this->productLinkService = $productLinkService;
        $this->productLinkMapper = $productLinkMapper;
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

            $productLinkProductSkus = [];
            foreach ($allVariationsBySkus as $sku => $variation) {
                $linkedProduct = $productLinks->getById($variation['organisationUnitId'] . '-' . $sku);
                if ($linkedProduct) {
                    foreach ($linkedProduct->getStockSkuMap() as $stockSku => $stockQty) {
                        $productLinkProductSkus[] = $stockSku;
                    }
                }
            }
            $productLinkProducts = $this->productService->fetchCollectionByOUAndSku([$ouId], $productLinkProductSkus);
            $parentProducts = $this->productService->fetchCollectionByOUAndId([$ouId], array_keys($productIds));
            foreach ($productLinkProducts as $product) {
                if ($product->getParentProductId() === 0) {
                    $parentProducts->attach($product);
                }
            }
        } catch(NotFound $e) {
            $productLinkProducts = [];
            $parentProducts = [];
        }

        $productLinksByProductId = [];
        foreach ($allVariationsBySkus as $sku => $variation) {
            $linkedProduct = $productLinks->getById($variation['organisationUnitId'].'-'.$sku);
            if ($linkedProduct) {
                foreach ($linkedProduct->getStockSkuMap() as $stockSku => $stockQty) {
                    $matchingProductLinkProducts = $productLinkProducts->getBy('sku', $stockSku);
                    if (count($matchingProductLinkProducts)) {
                        $matchingProductLinkProducts->rewind();
                        $productLinkProduct = $matchingProductLinkProducts->current();
                    }
                    if ($productLinkProduct) {
                        $id = $productLinkProduct->getParentProductId() > 0 ? $productLinkProduct->getParentProductId() : $productLinkProduct->getId();
                        $parentProduct = $parentProducts->getById($id);
                    }
                    /**
                     * instead of getting parent product of variation, need to get parent product of $stockSku
                     */
                    $productLinksByProductId[$variation['parentProductId']][$variation['id']][] = [
                        'sku' => $stockSku,
                        'quantity' => $stockQty,
                        'product' => $parentProduct ? $this->productMapper->getFullProductDataArray($parentProduct) : null,
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
