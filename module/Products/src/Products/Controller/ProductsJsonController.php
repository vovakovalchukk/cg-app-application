<?php

namespace Products\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Products\Service\ProductsService;
use CG_UI\View\Prototyper\JsonModelFactory;

class ProductsJsonController extends AbstractActionController
{
    const AJAX_ROUTE = 'AJAX';

    protected $productsService;
    protected $jsonModelFactory;

    public function __construct(ProductsService $productsService, JsonModelFactory $jsonModelFactory)
    {
        $this->setProductsService($productsService)
            ->setJsonModelFactory($jsonModelFactory);
    }

    public function ajaxAction()
    {
        $view = $this->getJsonModelFactory()->newInstance();
        $products = $this->getProductsService()->fetchProducts();
        return $view->setVariable('products', $products);
    }

    protected function setProductsService($productsService)
    {
        $this->productsService = $productsService;
        return $this;
    }

    /**
     * @return ProductsService
     */
    protected function getProductsService()
    {
        return $this->productsService;
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