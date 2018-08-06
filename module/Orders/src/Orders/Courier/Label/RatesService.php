<?php
namespace Orders\Courier\Label;

use CG\Channel\Shipping\Provider\Service\FetchRatesInterface;
use CG\Channel\Shipping\Provider\Service\ShippingRate\OrderRates\Collection as ShippingRateCollection;
use CG\Order\Shared\Courier\Label\OrderData\Collection as OrderDataCollection;
use CG\Order\Shared\Courier\Label\OrderItemsData\Collection as OrderItemsDataCollection;
use CG\Order\Shared\Courier\Label\OrderParcelsData\Collection as OrderParcelsDataCollection;
use CG\Order\Shared\Label\Status as OrderLabelStatus;

class RatesService extends ServiceAbstract
{
    const LOG_CODE = 'OrderCourierLabelRatesService';
    const LOG_UPDATE = 'Updating OrderLabel to rates fetched for Order %s';

    public function fetchRates(
        array $orderIds,
        OrderDataCollection $ordersData,
        OrderParcelsDataCollection $ordersParcelsData,
        OrderItemsDataCollection $ordersItemsData,
        int $shippingAccountId
    ): ShippingRateCollection {
        $rootOu = $this->userOUService->getRootOuByActiveUser();
        $this->addGlobalLogEventParams(['ou' => $rootOu->getId(), 'account' => $shippingAccountId]);

        try {
            $shippingAccount = $this->accountService->fetchShippingAccount((int) $shippingAccountId);
            /** @var FetchRatesInterface $carrier */
            $carrier = $this->getCarrierProviderService($shippingAccount);
            $orders = $this->getOrdersByIds($orderIds);

            $this->logDebug('Fetching shipping rates for orders %s with shipping account %d', [implode(',', $orderIds), $shippingAccountId], static::LOG_CODE);
            $shippingRates = $carrier->fetchRatesForOrders(
                $orders,
                $ordersData,
                $ordersParcelsData,
                $ordersItemsData,
                $rootOu,
                $shippingAccount
            );
            $orderLabels = $this->getOrCreateOrderLabelsForOrders(
                $orders,
                $ordersData,
                $ordersParcelsData,
                $shippingAccount
            );
            $this->updateOrderLabelStatus($orderLabels, OrderLabelStatus::RATES_FETCHED);
            return $shippingRates;
        } finally {
            $this->removeGlobalLogEventParams(['ou', 'account']);
        }
    }
}