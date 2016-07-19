<?php
namespace CG\CourierAdapter\Provider\Label;

use CG\Account\Shared\Entity as Account;
use CG\CourierAdapter\Provider\Implementation\Service as AdapterImplementationService;
use CG\CourierAdapter\Provider\Label\Mapper as CALabelMapper;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Label\Collection as OrderLabelCollection;
use CG\Order\Shared\Label\Filter as OrderLabelFilter;
use CG\Order\Shared\Label\Service as OrderLabelService;
use CG\Stdlib\Exception\Runtime\NotFound;

class Cancel
{
    /** @var AdapterImplementationService */
    protected $adapterImplementationService;
    /** @var CALabelMapper */
    protected $caLabelMapper;
    /** @var OrderLabelService */
    protected $orderLabelService;

    public function __construct(
        AdapterImplementationService $adapterImplementationService,
        CALabelMapper $caLabelMapper,
        OrderLabelService $orderLabelService
    ) {
        $this->setAdapterImplementationService($adapterImplementationService)
            ->setCALabelMapper($caLabelMapper)
            ->setOrderLabelService($orderLabelService);
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
        try {
            $orderLabel = $this->fetchOrderLabelForOrder($order);
        } catch (NotFound $e) {
            return false;
        }

        $courierInstance = $this->adapterImplementationService->getAdapterImplementationCourierInstanceForAccount($account);
        $deliveryService = $courierInstance->fetchDeliveryServiceByReference($orderLabel->getShippingServiceCode());

        $shipmentData = $this->caLabelMapper->ohOrderAndAccountToMinimalCAShipmentData($order, $account);
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

    protected function setAdapterImplementationService(AdapterImplementationService $adapterImplementationService)
    {
        $this->adapterImplementationService = $adapterImplementationService;
        return $this;
    }

    protected function setCALabelMapper(CALabelMapper $caLabelMapper)
    {
        $this->caLabelMapper = $caLabelMapper;
        return $this;
    }

    protected function setOrderLabelService(OrderLabelService $orderLabelService)
    {
        $this->orderLabelService = $orderLabelService;
        return $this;
    }
}
