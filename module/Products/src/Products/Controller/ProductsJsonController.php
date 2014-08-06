<?php

namespace Products\Controller;

use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Mvc\Controller\AbstractActionController;
use Products\Product\Service as ProductsService;
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
        $products = [];
        try {
            $products = $this->getProductsService()->fetchProducts();
        } catch(NotFound $e) {
            //noop
        }
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