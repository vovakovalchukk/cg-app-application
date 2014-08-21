<?php

namespace Products\Controller;

use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Mvc\Controller\AbstractActionController;
use Products\Product\Service as ProductService;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG\Product\Entity as ProductEntity;

class ProductsJsonController extends AbstractActionController
{
    const ROUTE_AJAX = 'AJAX';
    const ROUTE_STOCK_UPDATE = 'stockupdate';

    protected $productService;
    protected $jsonModelFactory;

    public function __construct(ProductService $productService, JsonModelFactory $jsonModelFactory)
    {
        $this->setProductsService($productService)
            ->setJsonModelFactory($jsonModelFactory);
    }

    public function ajaxAction()
    {
        $view = $this->getJsonModelFactory()->newInstance();
        $productsArray = [];
        try {
            $products = $this->getProductsService()->fetchProducts();
            foreach ($products as $product) {
                $productsArray[] = $this->toArrayProductEntityWithEmbeddedData($product);
            }
        } catch(NotFound $e) {
            //noop
        }
        return $view->setVariable('products', $productsArray);
    }

    protected function toArrayProductEntityWithEmbeddedData(ProductEntity $productEntity)
    {
        $product = $productEntity->toArray();
        $product = array_merge($product, [
            'images' => $productEntity->getImages()->toArray(),
            'listings' => $productEntity->getListings()->toArray()
        ]);
        $stockEntity = $productEntity->getStock();
        $product['stock'] = array_merge($productEntity->getStock()->toArray(), [
            'locations' => $stockEntity->getLocations()->toArray()
        ]);
        foreach ($productEntity->getVariations() as $variation) {
            $product['variations'][] = $this->toArrayProductEntityWithEmbeddedData($variation);
        }
        foreach ($product['stock']['locations'] as $stockLocationIndex => $stockLocation) {
            $stockLocationId = $product['stock']['locations'][$stockLocationIndex]['id'];
            $product['stock']['locations'][$stockLocationIndex]['eTag'] = $stockEntity->getLocations()->getById($stockLocationId)->getEtag();
        }
        return $product;
    }

    public function stockUpdateAction()
    {
        $stockLocation = $this->getProductsService()->updateStock(
            $this->params()->fromPost('stockLocationId'),
            $this->params()->fromPost('eTag'),
            $this->params()->fromPost('totalQuantity')
        );
        $view = $this->getJsonModelFactory()->newInstance();
        $view->setVariable('eTag', $stockLocation->getETag());
        return $view;
    }

    protected function setProductsService(ProductService $productService)
    {
        $this->productService = $productService;
        return $this;
    }

    protected function getProductsService()
    {
        return $this->productService;
    }

    protected function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }

    protected function getJsonModelFactory()
    {
        return $this->jsonModelFactory;
    }
}