<?php
namespace Reports\Order;

use CG\Account\Client\Entity as Account;
use CG\Order\Service\Filter as OrderFilter;
use CG\Reporting\Order\Filter as ReportOrderFilter;
use CG\Reporting\Order\Service as ReportOrderService;
use CG\Stdlib\Exception\Runtime\NotFound;
use Orders\Filter\Service as FilterService;
use Orders\Order\Service as OrderService;
use SetupWizard\Channels\Service as ChannelsService;

class Service
{
    const FILTER_TOTAL = 'total';
    const DEFAULT_POINTS_LIMIT = 100;
    const DIMENSION_CHANNEL = 'channel';

    /** @var ChannelsService */
    protected $channelsService;
    /** @var OrderService */
    protected $orderService;
    /** @var FilterService */
    protected $filterService;
    /** @var ReportOrderService */
    protected $reportOrderService;

    public function __construct(
        ChannelsService $channelsService,
        OrderService $orderService,
        ReportOrderService $reportOrderService,
        FilterService $filterService
    ) {
        $this->channelsService = $channelsService;
        $this->orderService = $orderService;
        $this->filterService = $filterService;
        $this->reportOrderService = $reportOrderService;
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
        string $dimension,
        array $metrics = [],
        array $orderFilters = [],
        int $limit = self::DEFAULT_POINTS_LIMIT
    ): array {
        $orderFilter = $this->buildOrderFilterFromArray($orderFilters);
        $orderFilter->setLimit($limit);
        $filter = new ReportOrderFilter($orderFilter, $dimension, $metrics);
        $reportOrderEntity = $this->reportOrderService->fetchByFilter($filter);
        return $reportOrderEntity->toArray();
    }

    public function buildOrderFilterFromArray(array $filterData)
    {
        return $this->addFiltersFromArray($this->buildDefaultOrderFilter(), $filterData);
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
            ->setOrganisationUnitId($this->orderService->getActiveUser()->getOuList());
    }

    protected function addFiltersFromArray(OrderFilter $filter, array $filters): OrderFilter
    {
        if (!empty($filters)) {
            $filter = $this->filterService->mergeFilters(
                $filter,
                $this->filterService->getFilterFromArray($filters)
            );
        }
        return $filter;
    }
}
