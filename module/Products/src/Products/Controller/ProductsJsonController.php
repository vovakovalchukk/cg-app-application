<?php

namespace Products\Controller;

use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Mvc\Controller\AbstractActionController;
use Products\Product\Service as ProductService;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG\Product\Entity as ProductEntity;
use CG\Product\Filter\Mapper as FilterMapper;

class ProductsJsonController extends AbstractActionController
{
    const ROUTE_AJAX = 'AJAX';
    const ROUTE_STOCK_UPDATE = 'stockupdate';
    const ROUTE_DELETE = 'Delete';

    protected $productService;
    protected $jsonModelFactory;
    protected $filterMapper;

    public function __construct(ProductService $productService, JsonModelFactory $jsonModelFactory, FilterMapper $filterMapper)
    {
        $this->setProductService($productService)
            ->setJsonModelFactory($jsonModelFactory)
            ->setFilterMapper($filterMapper);
    }

    public function ajaxAction()
    {
        $view = $this->getJsonModelFactory()->newInstance();
        $filterParams = $this->params()->fromPost('filter', []);
        if (!array_key_exists('deleted', $filterParams)) {
            $filterParams['deleted'] = false;
        }
        $requestFilter = $this->getFilterMapper()->fromArray($filterParams);
        $productsArray = [];
        try {
            $products = $this->getProductService()->fetchProducts($requestFilter);
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
        $stockLocation = $this->getProductService()->updateStock(
            $this->params()->fromPost('stockLocationId'),
            $this->params()->fromPost('eTag'),
            $this->params()->fromPost('totalQuantity')
        );
        $view = $this->getJsonModelFactory()->newInstance();
        $view->setVariable('eTag', $stockLocation->getETag());
        return $view;
    }

    public function deleteAction()
    {
        $view = $this->getJsonModelFactory()->newInstance();

        $productIds = $this->params()->fromPost('productIds');
        if (empty($productIds)){
            $view->setVariable('deleted', false);
            return $view;
        }

        $this->getProductService()->deleteProductsById($productIds);
        $view->setVariable('deleted', true);
        return $view;
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

    protected function setProductService(ProductService $productService)
    {
        $this->productService = $productService;
        return $this;
    }

    protected function getProductService()
    {
        return $this->productService;
    }

    protected function setFilterMapper(FilterMapper $filterMapper)
    {
        $this->filterMapper = $filterMapper;
        return $this;
    }

    protected function getFilterMapper()
    {
        return $this->filterMapper;
    }
}
