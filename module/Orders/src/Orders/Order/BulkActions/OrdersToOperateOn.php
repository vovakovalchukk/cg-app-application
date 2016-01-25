<?php
namespace Orders\Order\BulkActions;

use CG\Order\Client\Service as OrderService;
use CG\Order\Service\Filter;
use CG\Order\Service\Filter\StorageInterface as FilterStorage;
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
        $this->setOrderService($orderService)
            ->setFilterService($filterService)
            ->setActiveUserContainer($activeUserContainer)
            ->setFilterStorage($filterStorage);
    }

    public function __invoke(array $params, $orderBy = null, $orderDir = null)
    {
        $filterId = null;
        $saveFilter = false;
        $filter = $this->getBaseFilter()
            ->setOrderBy($orderBy)
            ->setOrderDirection($orderDir);

        if (isset($params['orders']) && is_array($params['orders']) && !empty($params['orders'])) {
            $filter->setOrderIds($params['orders']);

        } elseif (isset($params['filterId']) && $params['filterId'] != '') {
            $filterId = $params['filterId'];
            $filter = $this->applyFiltersFromFilterId($filter, $filterId);

        } elseif (isset($params['filter']) && is_array($params['filter']) && !empty($params['filter'])) {
            $filter = $this->applyFiltersFromFilterParam($filter, $params['filter']);
            $saveFilter = true;

        } else {
            throw new NotFound('No Order IDs or filters provided');
        }

        $collection = $this->orderService->fetchCollectionByFilter($filter);
        if ($saveFilter) {
            $savedFilter = $this->saveOrdersAsFilter($collection);
            $filterId = $savedFilter->getId();
        }
        $collection->setFilterId($filterId);
        return $collection;
    }

    protected function getBaseFilter()
    {
        return (new Filter())
            ->setLimit('all')
            ->setPage(1)
            ->setOrganisationUnitId($this->activeUserContainer->getActiveUser()->getOuList());
    }

    protected function applyFiltersFromFilterId(Filter $filter, $filterId)
    {
        try {
            $savedFilter = $this->filterStorage->fetch($filterId);
            return $this->filterService->mergeFilters(
                $filter,
                $savedFilter
            );

        } catch (NotFound $e) {
            return $this->applyFiltersFromFilterParam($filter, []);
        }
    }

    protected function applyFiltersFromFilterParam(Filter $filter, array $filterParam)
    {
        $filterParam = $this->filterService->addDefaultFiltersToArray($filterParam);
        return $this->filterService->mergeFilters(
            $filter,
            $this->filterService->getFilterFromArray($filterParam)
        );
    }

    protected function saveOrdersAsFilter(OrderCollection $orders)
    {
        $filter = $this->getBaseFilter()
            ->setOrderIds($orders->getIds());
        return $this->filterStorage->save($filter);
    }

    protected function setOrderService(OrderService $orderService)
    {
        $this->orderService = $orderService;
        return $this;
    }

    protected function setFilterService(FilterService $filterService)
    {
        $this->filterService = $filterService;
        return $this;
    }

    protected function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    protected function setFilterStorage(FilterStorage $filterStorage)
    {
        $this->filterStorage = $filterStorage;
        return $this;
    }
}