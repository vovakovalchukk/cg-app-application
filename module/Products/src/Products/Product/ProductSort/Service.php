<?php
namespace Products\Product\ProductSort;

use CG\Product\ProductSort\Client\Service as ProductSortService;
use CG\Product\ProductSort\Entity as ProductSort;
use CG\Product\ProductSort\Filter;
use CG\Product\ProductSort\Mapper as ProductSortMapper;
use CG\Stdlib\Exception\Runtime\NotFound;

class Service
{
    /** @var ProductSortService $productSortService */
    protected $productSortService;
    /** @var ProductSortMapper */
    protected $mapper;

    public function __construct(ProductSortService $productSortService, ProductSortMapper $mapper)
    {
        $this->setProductSortService($productSortService)
            ->setMapper($mapper);
    }

    /**
     * @return ProductSortMapper
     */
    public function getMapper(): ProductSortMapper
    {
        return $this->mapper;
    }

    /**
     * @param ProductSortMapper $mapper
     * @return self
     */
    public function setMapper(ProductSortMapper $mapper): self
    {
        $this->mapper = $mapper;
        return $this;
    }

    /**
     * @return ProductSortService
     */
    public function getProductSortService(): ProductSortService
    {
        return $this->productSortService;
    }

    /**
     * @param ProductSortService $productSortService
     * @return self
     */
    public function setProductSortService(ProductSortService $productSortService): self
    {
        $this->productSortService = $productSortService;
        return $this;
    }

    public function save(array $filterData)
    {
        $filter = $this->newProductSortFilter($filterData);
        try {
            $entity = $this->fetchProductSortByFilter($filter);
            if (!empty($filterData['userId']) && empty($entity->getUserId())) { // existing filter is for all users, we need it for the current user
                $entity = $this->newProductSort($filterData);
            } else {
                $entity->setData($filterData['data']);
            }
        } catch (NotFound $exception) {
            $entity = $this->newProductSort($filterData);
        }

        $this->getProductSortService()->save($entity);
    }

    protected function newProductSortFilter(array $filterData): Filter
    {
        return new Filter(
            null,
            null,
            null,
            $filterData['organisationUnitId'],
            $filterData['userId']
        );
    }

    protected function newProductSort(array $entityData): ProductSort
    {
        return $this->getMapper()->fromArray($entityData);
    }

    protected function fetchProductSortByFilter(Filter $filter): ?ProductSort
    {
        return $this->getProductSortService()->fetchEntityByFilter($filter);
    }

    public function getProductSort(int $userId, int $organisationUnitId): ?ProductSort
    {
        $filter = $this->newProductSortFilter(['userId' => $userId, 'organisationUnitId' => $organisationUnitId]);
        return $this->fetchProductSortByFilter($filter);
    }
}
