<?php
namespace Reports\Sales;

use CG\Account\Client\Entity as Account;
use CG\Stdlib\DateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use Orders\Filter\Service as FilterService;
use CG\Order\Service\Filter as OrderFilter;
use Orders\Order\Service as OrderService;
use Reports\OrderCount\UnitService;
use SetupWizard\Channels\Service as ChannelsService;
use Reports\OrderCount\Service as OrderCountService;

class Service
{
    const FILTER_TOTAL = 'total';

    /** @var  ChannelsService */
    protected $channelsService;
    /** @var  OrderService */
    protected $orderService;
    /** @var  FilterService */
    protected $filterService;
    /** @var OrderCountService */
    protected $orderCountService;
    /** @var UnitService  */
    protected $unitService;

    public function __construct(
        ChannelsService $channelsService,
        OrderService $orderService,
        FilterService $filterService,
        OrderCountService $orderCountService,
        UnitService $unitService
    ) {
        $this->channelsService = $channelsService;
        $this->orderService = $orderService;
        $this->filterService = $filterService;
        $this->orderCountService = $orderCountService;
        $this->unitService = $unitService;
    }

    public function getChannelsForActiveUser(): array
    {
        try {
            $accounts = $this->channelsService->fetchAccountsForActiveUser();
        } catch (NotFound $e) {
            return [];
        }

        $channels = [];
        /** @var Account $account */
        foreach ($accounts as $account) {
            if (isset($channels[$account->getChannel()])) {
                continue;
            }
            $channels[$account->getChannel()] = $this->buildChannelDetails($account->getChannel());
        }

        return $channels;
    }

    public function getTotalFilter(): array
    {
        return $this->buildChannelDetails(static::FILTER_TOTAL);
    }

    public function getOrderCountsData(
        array $filters = [],
        array $strategy = [],
        array $strategyType = [],
        string $unitType = UnitService::UNIT_DAY
    ) {
        $orders = $this->fetchOrdersByFilter(
            $this->addFiltersFromArray(
                $this->buildDefaultOrderFilter(), $filters
            )
        );

        if (empty($orders)) {
            return [];
        }

        return $this->orderCountService->buildOrderCounts(
            $orders,
            $unitType,
            $strategy,
            $strategyType
        );
    }

    public function getDateUnitByFilters(array $filters): array
    {
        try {
            $filter = $this->addFiltersFromArray($this->buildDefaultOrderFilter(), $filters);
            if ($filter->getPurchaseDateTo() && $filter->getPurchaseDateFrom()) {
                $start = $filter->getPurchaseDateFrom();
                $end = $filter->getPurchaseDateTo();
            } else {
                [$start, $end] = $this->getOrdersDateRange($filter);
            }

            return $this->unitService->buildDateUnitFromDates(new DateTime($start), new DateTime($end));
        } catch (NotFound $e) {
            return [];
        }
    }

    protected function getOrdersDateRange($filter): array
    {
        $orders = $this->orderService->getOrders($filter);
        $startDate = $orders->getFirst()->getPurchaseDate();
        // move iterator cursor to the end
        foreach ($orders as $order) {
            // no-op
        }
        $endDate = $order->getPurchaseDate();

        return [$startDate, $endDate];
    }

    protected function buildChannelDetails(string $channel): array
    {
        return [
            'id' => 'channel-' . $channel,
            'channel' => $channel,
            'name' => $channel
        ];
    }

    protected function buildDefaultOrderFilter()
    {
        return $this->filterService->getFilter()
            ->setOrganisationUnitId($this->orderService->getActiveUser()->getOuList())
            ->setLimit('all')
            ->setPage(1)
            ->setOrderBy('purchaseDate')
            ->setOrderDirection('ASC');
    }

    protected function addFiltersFromArray(OrderFilter $filter, array $filters): OrderFilter
    {
        $requestFilter = $this->filterService->addDefaultFiltersToArray($filters);
        if (!empty($requestFilter)) {
            $filter = $this->filterService->mergeFilters(
                $filter,
                $this->filterService->getFilterFromArray($requestFilter)
            );
        }
        return $filter;
    }

    protected function fetchOrdersByFilter(OrderFilter $filter)
    {
        try {
            return $this->orderService->getOrders($filter);
        } catch (NotFound $e) {
            return [];
        }
    }
}
