<?php
namespace Products\Service;

use CG\Product\Service as ProductService;
use CG\User\ActiveUserInterface;

class ProductsService
{
    const LIMIT = 20;
    const PAGE = 1;

    protected $productService;
    protected $activeUserContainer;

    public function __construct(ProductService $productService, ActiveUserInterface $activeUserContainer)
    {
        $this->setProductService($productService)
            ->setActiveUserContainer($activeUserContainer);
    }

    public function fetchProducts()
    {
        $products = $this->getProductService()->fetchCollectionByPagination(
            static::LIMIT,
            static::PAGE,
            $this->getActiveUserContainer()->getActiveUser()->getOuList()
        );
        return $products;
    }

    protected function setProductService(ProductService $productService)
    {
        $this->productService = $productService;
        return $this;
    }

    /**
     * @return ProductService
     */
    protected function getProductService()
    {
        return $this->productService;
    }

    protected function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    /**
     * @return ActiveUserInterface
     */
    protected function getActiveUserContainer()
    {
        return $this->activeUserContainer;
    }
}