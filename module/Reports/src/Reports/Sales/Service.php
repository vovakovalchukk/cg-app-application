<?php
namespace Reports\Sales;

use CG\Account\Client\Entity as Account;
use CG\Stdlib\DateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use Orders\Filter\Service as FilterService;
use Orders\Order\Service as OrderService;
use SetupWizard\Channels\Service as ChannelsService;
use CG\Order\Shared\Entity as Order;

class Service
{
    const FILTER_TOTAL = 'total';

    /** @var  ChannelsService */
    protected $channelsService;
    /** @var  OrderService */
    protected $orderService;
    /** @var  FilterService */
    protected $filterService;

    public function __construct(ChannelsService $channelsService, OrderService $orderService, FilterService $filterService)
    {
        $this->channelsService = $channelsService;
        $this->orderService = $orderService;
        $this->filterService = $filterService;
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

    public function getOrderCountsData()
    {
        $filter = $this->filterService->getFilter()
            ->setOrganisationUnitId($this->orderService->getActiveUser()->getOuList())
            ->setLimit('all')
            ->setPage(1)
            ->setOrderBy('purchaseDate')
            ->setOrderDirection('ASC');
        $orders = $this->orderService->getOrders($filter);

        $orderCounts = [];
        /** @var Order $order */
        foreach ($orders as $order) {
            $orderDate = (new DateTime($order->getPurchaseDate()))->format(DateTime::FORMAT_DATE);
            $orderCounts[$orderDate] = isset($orderCounts[$orderDate]) ? $orderCounts[$orderDate]++ : 1;
            $orderCounts[$orderDate]++;
        }

        $dataset = [];
        foreach ($orderCounts as $date => $count) {
            $dataset[] = [
                'x' => $date,
                'y' => $count
            ];
        }

        return $dataset;
    }

    protected function buildChannelDetails(string $channel): array
    {
        return [
            'id' => 'channel-' . $channel,
            'channel' => $channel,
            'name' => $channel
        ];
    }
}
