<?php

namespace Products\Controller;

use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Mvc\Controller\AbstractActionController;
use Products\Product\Service as ProductService;
use CG_UI\View\Prototyper\JsonModelFactory;

class ProductsJsonController extends AbstractActionController
{
    const ROUTE_AJAX = 'AJAX';

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
        $products = [];
        try {
            $products = $this->getProductsService()->fetchProducts();
        } catch(NotFound $e) {
            //noop
        }
        
        return $view->setVariable('products', $products);
    }

    protected function setProductsService($productService)
    {
        $this->productService = $productService;
        return $this;
    }

    /**
     * @return ProductsService
     */
    protected function getProductsService()
    {
        return $this->productService;
    }

    protected function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }

    /**
     * @return JsonModelFactory
     */
    protected function getJsonModelFactory()
    {
        return $this->jsonModelFactory;
    }
}