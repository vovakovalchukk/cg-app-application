<?php
namespace Products\Product\ProductSort;

use CG\Product\ProductSort\Client\Service as ProductSortService;
use CG\Product\ProductSort\Filter;
use CG\Product\ProductSort\Mapper;
use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Di\Di;

class Service
{
    /** @var ProductSortService $productSortService */
    protected $productSortService;

    /** @var Di $di */
    protected $di;

    public function __construct(
        Di                    $di,
        ProductSortService $productSortService
    )
    {
        $this->setDi($di)
            ->setProductSortService($productSortService);
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

    protected function newProductSortFilter(array $filterData)
    {
        return $this->getDi()->newInstance(Filter::class, $filterData);
    }

    protected function newProductSort(array $entityData)
    {
        return $this->getDi()->newInstance(Mapper::class)->fromArray($entityData);
    }

    protected function fetchProductSortByFilter(Filter $filter)
    {
        return $this->getProductSortService()->fetchEntityByFilter($filter);
    }

    public function getProductSort(int $userId, int $organisationUnitId)
    {
        $filter = $this->newProductSortFilter(['userId' => $userId, 'organisationUnitId' => $organisationUnitId]);
        return $this->fetchProductSortByFilter($filter);
    }
}
