<?php
namespace Products\Product\ProductFilters;

use CG\Product\ProductFilter\Client\Service as ProductFiltersService;
use CG\Product\ProductFilter\Filter;
use CG\Product\ProductFilter\Mapper;
use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Di\Di;

class Service
{
    /** @var ProductFiltersService $productFiltersService */
    protected $productFiltersService;

    /** @var Di $di */
    protected $di;

    public function __construct(
        Di                    $di,
        ProductFiltersService $productFiltersService
    )
    {
        $this->setDi($di)
            ->setProductFiltersService($productFiltersService);
    }

    /**
     * @return ProductFiltersService
     */
    public function getProductFiltersService(): ProductFiltersService
    {
        return $this->productFiltersService;
    }

    /**
     * @param ProductFiltersService $productFiltersService
     * @return self
     */
    public function setProductFiltersService(ProductFiltersService $productFiltersService): self
    {
        $this->productFiltersService = $productFiltersService;
        return $this;
    }

    /**
     * @return Di
     */
    public function getDi(): Di
    {
        return $this->di;
    }

    /**
     * @param Di $di
     * @return self
     */
    public function setDi(Di $di): self
    {
        $this->di = $di;
        return $this;
    }

    public function save(array $filterData)
    {
        $filter = $this->newProductFilterFilter($filterData);
        try {
            $entity = $this->fetchProductFilterByFilter($filter);
            if (!empty($filterData['userId']) && empty($entity->getUserId())) { // existing filter is for all users, we need it for the current user
                $entity = $this->newProductFilter($filterData);
            } else {
                $entity->setFilters($filterData['filters']);
            }
        } catch (NotFound $exception) {
            $entity = $this->newProductFilter($filterData);
        }

        $this->getProductFiltersService()->save($entity);
    }

    protected function newProductFilterFilter(array $filterData)
    {
        return $this->getDi()->newInstance(Filter::class, $filterData);
    }

    protected function newProductFilter(array $entityData)
    {
        return $this->getDi()->newInstance(Mapper::class)->fromArray($entityData);
    }

    protected function fetchProductFilterByFilter(Filter $filter)
    {
        return $this->getProductFiltersService()->fetchEntityByFilter($filter);
    }

    public function getProductFilter(int $userId, int $organisationUnitId)
    {
        $filter = $this->newProductFilterFilter(['userId' => $userId, 'organisationUnitId' => $organisationUnitId, 'defaultFilter' => true]);
        return $this->fetchProductFilterByFilter($filter);
    }
}
