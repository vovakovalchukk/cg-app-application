<?php
namespace CG\CourierAdapter\Provider\Label;

use CG\Account\Shared\Entity as Account;
use CG\CourierAdapter\CourierInterface;
use CG\CourierAdapter\Provider\Implementation\Service as AdapterImplementationService;
use CG\CourierAdapter\Provider\Label\Mapper as CALabelMapper;
use CG\CourierAdapter\Shipment\CancellingInterface;
use CG\Order\Client\Service as OrderService;
use CG\Order\Service\Filter as OrderFilter;
use CG\Order\Shared\ShippableInterface as Order;
use CG\Order\Shared\Label\Collection as OrderLabelCollection;
use CG\Order\Shared\Label\Entity as OrderLabel;
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
    /** @var OrderService */
    protected $orderService;

    public function __construct(
        AdapterImplementationService $adapterImplementationService,
        CALabelMapper $caLabelMapper,
        OrderLabelService $orderLabelService,
        OrderService $orderService
    ) {
        $this->setAdapterImplementationService($adapterImplementationService)
            ->setCALabelMapper($caLabelMapper)
            ->setOrderLabelService($orderLabelService)
            ->setOrderService($orderService);
    }

    /**
     * @return null
     */
    public function cancelOrderLabels(OrderLabelCollection $orderLabels, Account $account)
    {
        $courierInstance = $this->adapterImplementationService->getAdapterImplementationCourierInstanceForAccount($account);
        if (!$courierInstance instanceof CancellingInterface) {
            throw new \RuntimeException('Request to cancel labels but the courier instance does not support it');
        }

        $orders = $this->fetchOrdersForOrderLabels($orderLabels);
        foreach ($orderLabels as $orderLabel) {
            $order = $orders->getById($orderLabel->getOrderId());
            $shipment = $this->createShipmentFromOrderAndLabel($order, $orderLabel, $account, $courierInstance);
            $courierInstance->cancelShipment($shipment);
        }
    }

    /**
     * @return bool
     */
    public function isCancellationAllowedForOrder(Account $account, Order $order)
    {
        $courierInstance = $this->adapterImplementationService->getAdapterImplementationCourierInstanceForAccount($account);
        if (!$courierInstance instanceof CancellingInterface) {
            return false;
        }

        try {
            $orderLabel = $this->orderLabelService->fetchNonCancelledOrderLabelForOrder($order);
        } catch (NotFound $e) {
            return false;
        }

        $shipment = $this->createShipmentFromOrderAndLabel($order, $orderLabel, $account, $courierInstance);

        return $shipment->isCancellable();
    }

    protected function fetchOrdersForOrderLabels(OrderLabelCollection $orderLabels)
    {
        $filter = (new OrderFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setOrderIds($orderLabels->getArrayOf('orderId'));
        return $this->orderService->fetchCollectionByFilter($filter);
    }

    protected function createShipmentFromOrderAndLabel(
        Order $order,
        OrderLabel $orderLabel,
        Account $account,
        CourierInterface $courierInstance
    ) {
        $deliveryService = $courierInstance->fetchDeliveryServiceByReference($orderLabel->getShippingServiceCode());
        $shipmentData = $this->caLabelMapper->ohOrderAndAccountToMinimalCAShipmentData($order, $account);
        $shipment = $deliveryService->createShipment($shipmentData);
        $shipment->setCourierReference($orderLabel->getExternalId());

        return $shipment;
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

    protected function setOrderService(OrderService $orderService)
    {
        $this->orderService = $orderService;
        return $this;
    }
}
