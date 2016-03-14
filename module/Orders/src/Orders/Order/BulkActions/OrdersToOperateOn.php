<?php
namespace Orders\Order\BulkActions;

use CG\Order\Client\Service as OrderService;
use CG\Order\Service\Filter\StorageInterface as FilterStorage;
use CG\Order\Service\Filter;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\ActiveUserInterface;
use Orders\Filter\Service as FilterService;

class OrdersToOperateOn
{
    /** @var OrderService */
    protected $orderService;
    /** @var FilterService */
    protected $filterService;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var FilterStorage */
    protected $filterStorage;

    public function __construct(
        OrderService $orderService,
        FilterService $filterService,
        ActiveUserInterface $activeUserContainer,
        FilterStorage $filterStorage
    ) {
        $this
            ->setOrderService($orderService)
            ->setFilterService($filterService)
            ->setActiveUserContainer($activeUserContainer)
            ->setFilterStorage($filterStorage);
    }

    /**
     * @return OrderCollection
     */
    public function __invoke(array $params, $orderBy = null, $orderDir = null)
    {
        $filter = $this->buildFilterFromInput($params, $orderBy, $orderDir);

        /** @var OrderCollection $collection */
        $collection = $this->orderService->fetchCollectionByFilter($filter);
        $collection->setFilterId($filter->getId());
        return $collection;
    }

    /**
     * @return Filter
     */
    public function buildFilterFromInput(array $params, $orderBy = null, $orderDir = null)
    {
        $filter = $this->getBaseFilter()
            ->setOrderBy($orderBy)
            ->setOrderDirection($orderDir);

        if (isset($params['orders']) && is_array($params['orders']) && !empty($params['orders'])) {
            $filter->setOrderIds($params['orders']);

        } elseif (isset($params['filterId']) && $params['filterId'] != '') {
            $filter = $this->applyFiltersFromFilterId($filter, $params['filterId']);

        } elseif (isset($params['filter']) && is_array($params['filter']) && !empty($params['filter'])) {
            $filter = $this->applyFiltersFromFilterParam($filter, $params['filter']);
            $filter = $this->saveFilterAsOrderIds($filter);

        } else {
            throw new NotFound('No Order IDs or filters provided');
        }
        return $filter;
    }

    /**
     * @return Filter
     */
    protected function getBaseFilter()
    {
        return (new Filter())
            ->setLimit('all')
            ->setPage(1)
            ->setOrganisationUnitId($this->activeUserContainer->getActiveUser()->getOuList());
    }

    /**
     * @return Filter
     */
    protected function applyFiltersFromFilterId(Filter $filter, $filterId)
    {
        $filter->setId($filterId)
            ->setConvertToOrderIds(true);
        return $filter;
    }

    /**
     * @return Filter
     */
    protected function applyFiltersFromFilterParam(Filter $filter, array $filterParam)
    {
        $filterParam = $this->filterService->addDefaultFiltersToArray($filterParam);
        return $this->filterService->mergeFilters(
            $filter,
            $this->filterService->getFilterFromArray($filterParam)
        );
    }

    /**
     * @return Filter
     */
    protected function saveFilterAsOrderIds(Filter $filter)
    {
        $limit = $filter->getLimit();
        $page = $filter->getPage();
        $orderBy = $filter->getOrderBy();
        $orderDir = $filter->getOrderDirection();

        $filter->setConvertToOrderIds(true);
        /** @var Filter $filter */
        $filter = $this->filterStorage->save($filter);

        // Saving a filter strips the pagination and ordering so we can change it later, need to re-add it
        $filter->setLimit($limit)
            ->setPage($page)
            ->setOrderBy($orderBy)
            ->setOrderDirection($orderDir);
        return $filter;
    }

    /**
     * @return self
     */
    protected function setOrderService(OrderService $orderService)
    {
        $this->orderService = $orderService;
        return $this;
    }

    /**
     * @return self
     */
    protected function setFilterService(FilterService $filterService)
    {
        $this->filterService = $filterService;
        return $this;
    }

    /**
     * @return self
     */
    protected function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    /**
     * @return self
     */
    protected function setFilterStorage(FilterStorage $filterStorage)
    {
        $this->filterStorage = $filterStorage;
        return $this;
    }
}
