<?php
namespace Orders\Courier\Label;

use CG\Channel\Shipping\Provider\Service\FetchRatesInterface;
use CG\Channel\Shipping\Provider\Service\ShippingRate\OrderRates\Collection as ShippingRateCollection;
use CG\Order\Shared\Courier\Label\OrderData\Collection as OrderDataCollection;
use CG\Order\Shared\Courier\Label\OrderItemsData\Collection as OrderItemsDataCollection;
use CG\Order\Shared\Courier\Label\OrderParcelsData\Collection as OrderParcelsDataCollection;

class RatesService extends ServiceAbstract
{
    const LOG_CODE = 'OrderCourierLabelRatesService';

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
            return $carrier->fetchRatesForOrders(
                $orders,
                $ordersData,
                $ordersParcelsData,
                $ordersItemsData,
                $rootOu,
                $shippingAccount
            );

        } finally {
            $this->removeGlobalLogEventParams(['ou', 'account']);
        }
    }
}