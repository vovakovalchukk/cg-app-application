<?php
namespace CG\CourierAdapter\Provider\Label;

use CG\Account\Shared\Entity as Account;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Label\Collection as OrderLabelCollection;
use CG\Order\Shared\Label\Filter as OrderLabelFilter;
use CG\Order\Shared\Label\Service as OrderLabelService;

class Cancel
{
    /** @var OrderLabelService */
    protected $orderLabelService;

    public function __construct(OrderLabelService $orderLabelService)
    {
        $this->setOrderLabelService($orderLabelService);
    }

    public function cancelOrderLabels(OrderLabelCollection $orderLabels, Account $shippingAccount)
    {
        // TODO
    }

    /**
     * @return bool
     */
    public function isCancellationAllowedForOrder(Account $account, Order $order)
    {
        $orderLabel = $this->fetchOrderLabelForOrder($order);
        $courierInstance = $this->adapterImplementationService->getAdapterImplementationCourierInstanceForAccount($account);
        $deliveryService = $courierInstance->fetchDeliveryServiceByReference($orderLabel->getShippingServiceCode());
        $shipmentClass = $deliveryService->getShipmentClass();
        $shipmentData = $this->caLabelMapper->ohOrderAndDataToCAShipmentData(
            $order, [], $account, $order->getOrganisationUnitId(), $shipmentClass
        );
        $shipment = $deliveryService->createShipment($shipmentData);
        $shipment->setCourierReference($orderLabel->getExternalId());

        return $shipment->isCancellable();
    }

    protected function fetchOrderLabelForOrder(Order $order)
    {
        $filter = (new OrderLabelFilter())
            ->setLimit(1)
            ->setPage(1)
            ->setOrderId([$order->getId()]);
        $labels = $this->orderLabelService->fetchCollectionByFilter($filter);
        $labels->rewind();
        return $labels->current();
    }

    protected function setOrderLabelService(OrderLabelService $orderLabelService)
    {
        $this->orderLabelService = $orderLabelService;
        return $this;
    }
}
