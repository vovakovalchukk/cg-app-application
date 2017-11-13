<?php
namespace Orders\Filter;

use CG\Order\Service\Filter\Mapper;
use CG\Order\Service\Filter;
use CG\Order\Shared\Shipping\Conversion\Service as ShippingConversionService;
use Zend\Session\ManagerInterface;

class Service
{
    const FILTER_SHIPPING_METHOD_NAME = "shippingMethod";
    const FILTER_SHIPPING_ALIAS_NAME = "shippingAliasId";

    protected $filter;
    protected $mapper;
    protected $persistentStorage;
    /** @var ShippingConversionService */
    protected $shippingConversionService;

    public function __construct(
        Filter $filter,
        Mapper $mapper,
        ManagerInterface $persistentStorage,
        ShippingConversionService $shippingConversionService
    ) {
        $this
            ->setFilter($filter)
            ->setMapper($mapper)
            ->setPersistentStorage($persistentStorage)
            ->setShippingConversionService($shippingConversionService);
    }

    /**
     * @return self
     */
    public function setFilter(Filter $filter)
    {
        $this->filter = $filter;
        return $this;
    }

    /**
     * @return Filter
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @return self
     */
    public function setMapper(Mapper $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    /**
     * @return Mapper
     */
    public function getMapper()
    {
        return $this->mapper;
    }

    /**
     * @return self
     */
    public function setPersistentStorage(ManagerInterface $persistentStorage)
    {
        $this->persistentStorage = $persistentStorage;
        return $this;
    }

    /**
     * @return ManagerInterface
     */
    public function getPersistentStorage()
    {
        return $this->persistentStorage;
    }

    /**
     * @return self
     */
    public function setPersistentFilter(DisplayFilter $filter)
    {
        $storage = $this->getPersistentStorage()->getStorage();

        if (!isset($storage['orders'])) {
            $storage['orders'] = [];
        }

        $storage['orders']['filter'] = $filter;

        return $this;
    }

    /**
     * @return DisplayFilter
     */
    public function getPersistentFilter()
    {
        $storage = $this->getPersistentStorage()->getStorage();

        if (!isset($storage['orders'])) {
            $storage['orders'] = [];
        }

        if (!isset($storage['orders']['filter'])) {
            $storage['orders']['filter'] = $this->createDisplayFilter();
        }

        if ($storage['orders']['filter'] instanceof Filter) {
            $storage['orders']['filter'] = $this->createDisplayFilter($storage['orders']['filter']);
        }

        if (!($storage['orders']['filter'] instanceof DisplayFilter)) {
            $storage['orders']['filter'] = $this->createDisplayFilter();
        }

        return $storage['orders']['filter'];
    }

    /**
     * @return DisplayFilter
     */
    public function createDisplayFilter(Filter $filter = null)
    {
        return new DisplayFilter(
            [],
            $filter ?: $this->getFilterFromArray([])
        );
    }

    /**
     * @return Filter
     */
    public function getFilterFromArray(array $data)
    {
        return $this->getMapper()->fromArray($data);
    }

    /**
     * @return Filter
     */
    public function mergeFilters(Filter $filter1, Filter $filter2)
    {
        return $this->getMapper()->merge($filter1, $filter2);
    }

    /**
     * @return array
     */
    public function addDefaultFiltersToArray(array $filters)
    {
        if (!isset($filters['archived'])) {
            $filters['archived'] = [false];
        }

        $filters['hasItems'] = [true];

        if (isset($filters[static::FILTER_SHIPPING_ALIAS_NAME])) {
            $methodNames = $this->shippingConversionService
                ->fromAliasIdsToMethodNames($filters[static::FILTER_SHIPPING_ALIAS_NAME]);
            $filters[static::FILTER_SHIPPING_METHOD_NAME] = $methodNames;
        }

        return $filters;
    }

    /**
     * @return self
     */
    protected function setShippingConversionService(ShippingConversionService $shippingConversionService)
    {
        $this->shippingConversionService = $shippingConversionService;
        return $this;
    }
}
