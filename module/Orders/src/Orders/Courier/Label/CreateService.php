<?php
namespace Orders\Courier\Label;

use CG\Account\Shared\Entity as Account;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Item\Collection as ItemCollection;
use CG\Order\Shared\Item\Entity as Item;
use CG\Order\Shared\Label\Collection as OrderLabelCollection;
use CG\Order\Shared\Label\Entity as OrderLabel;
use CG\Order\Shared\Label\Filter as OrderLabelFilter;
use CG\Order\Shared\Label\Status as OrderLabelStatus;
use CG\Order\Shared\ShippableInterface as Order;
use CG\Product\Detail\Entity as ProductDetail;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Runtime\ValidationMessagesException;

class CreateService extends ServiceAbstract
{
    const LOG_CODE = 'OrderCourierLabelCreateService';
    const LOG_CREATE = 'Create label request for Order(s) %s, shipping Account %d';
    const LOG_CREATE_SEND = 'Sending create request to carrier provider for Order(s) %s, shipping Account %d';
    const LOG_CREATE_DONE = 'Completed create label request for Order(s) %s, shipping Account %d';
    const LOG_GET_LABEL_ATTEMPT = 'Attempt %d to get label data for order number %s, Order %s, shipping Account %d';
    const LOG_GET_LABEL_RETRY = 'No label data found on this attempt, will retry for order number %s, Order %s, shipping Account %d. Giving up.';
    const LOG_GET_LABEL_FAILED = 'Max attempts (%d) to get label data reached for order number %s, Order %s, shipping Account %d. Giving up.';
    const LOG_GET_TRACKING = 'Looking for tracking numbers for order number %s, Order %s, shipping Account %d.';
    const LOG_GET_TRACKING_FOUND = 'Found tracking number %s for Order %s.';
    const LOG_GET_TRACKING_SAVE = 'Saving tracking numbers for Order %s.';
    const LOG_UPDATE_ORDER_LABEL = 'Updating OrderLabel with PDF data for Order %s (attempt %d)';

    public function createForOrdersData(
        array $orderIds,
        array $ordersData,
        array $orderParcelsData,
        array $ordersItemsData,
        $shippingAccountId
    ) {
        $orderIdsString = implode(',', $orderIds);
        $rootOu = $this->userOUService->getRootOuByActiveUser();
        $user = $this->userOUService->getActiveUser();
        $this->addGlobalLogEventParam('account', $shippingAccountId)->addGlobalLogEventParam('ou', $rootOu->getId());
        $this->logDebug(static::LOG_CREATE, [$orderIdsString, $shippingAccountId], static::LOG_CODE);
        $shippingAccount = $this->accountService->fetchShippingAccount($shippingAccountId);
        $orders = $this->getOrdersByIds($orderIds);
        $this->removeZeroQuantityItemsFromOrders($orders);

        $this->persistProductDetailsForOrders($orders, $orderParcelsData, $ordersItemsData, $rootOu);

        $orderLabelsData = $this->createOrderLabelsForOrders($orders, $ordersData, $orderParcelsData, $shippingAccount);
        if (count($orderLabelsData['orderLabels']) == 0) {
            return $orderLabelsData['errors'];
        }
        if (!empty($orderLabelsData['errors'])) {
            $orders = $this->removeOrdersWithNoOrderLabel($orders, $orderLabelsData['errors']);
        }
        $orderLabels = $orderLabelsData['orderLabels'];

        $ordersItemsData = $this->ensureOrderItemsData($orders, $ordersItemsData, $orderParcelsData);

        try {
            $this->logDebug(static::LOG_CREATE_SEND, [$orderIdsString, $shippingAccountId], static::LOG_CODE);
            $labelReadyStatuses = $this->getCarrierProviderService($shippingAccount)->createLabelsForOrders(
                $orders,
                $orderLabels,
                $ordersData,
                $orderParcelsData,
                $ordersItemsData,
                $rootOu,
                $shippingAccount,
                $user
            );
            $this->unlockOrderLabels();
            $this->logDebug(static::LOG_CREATE_DONE, [$orderIdsString, $shippingAccountId], static::LOG_CODE);
            $this->removeGlobalLogEventParam('account')->removeGlobalLogEventParam('ou');

            if (!empty($orderLabelsData['errors'])) {
                $labelReadyStatuses = array_merge($orderLabelsData['errors'], $labelReadyStatuses);
            }
            return $labelReadyStatuses;
        } catch (\Exception $e) {
            // Unlock the labels so the user can try again
            $this->unlockOrderLabels();
            throw $e;
        }
    }

    protected function createOrderLabelsForOrders(OrderCollection $orders, array $ordersData, array $orderParcelsData, Account $shippingAccount)
    {
        $orderLabelsData = [
            'orderLabels' => new OrderLabelCollection(OrderLabel::class, __FUNCTION__, ['orderId' => $orders->getIds()]),
            'errors' => [],
        ];

        foreach ($orders as $order) {
            $orderData = $ordersData[$order->getId()];
            $parcelsData = $orderParcelsData[$order->getId()] ?? [];
            try {
                $orderLabelsData['orderLabels']->attach(
                    $this->createOrderLabelForOrder($order, $orderData, $parcelsData, $shippingAccount)
                );
            } catch (ValidationMessagesException $exception) {
                $orderLabelsData['errors'][$order->getId()] = $exception;
            }
        }
        return $orderLabelsData;
    }

    protected function createOrderLabelForOrder(
        Order $order,
        array $orderData,
        array $orderParcelsData,
        Account $shippingAccount
    ) {
        $orderLabel = parent::createOrderLabelForOrder(
            $order,
            $orderData,
            $orderParcelsData,
            $shippingAccount
        );

        // Check this label doesnt already exist before we try to create it
        // This needs to happen inside the lock to prevent duplication
        if ($this->doesOrderLabelExistForOrder($order)) {
            $this->unlockOrderLabel($orderLabel);
            throw (new ValidationMessagesException(0))->addErrorWithField($order->getId().':Duplicate', 'There is already a label for this order');
        }

        return $orderLabel;
    }

    /**
     * @return boolean
     */
    protected function doesOrderLabelExistForOrder(Order $order)
    {
        $notCancelled = OrderLabelStatus::getAllStatuses();
        unset($notCancelled[OrderLabelStatus::CANCELLED]);

        try {
            $filter = (new OrderLabelFilter())
                ->setLimit('all')
                ->setPage(1)
                ->setOrderId([$order->getId()])
                ->setStatus(array_values($notCancelled));

            $this->orderLabelService->fetchCollectionByFilter($filter);
            return true;
        } catch (NotFound $ex) {
            return false;
        }
    }

    protected function removeOrdersWithNoOrderLabel(OrderCollection $orders, array $labelErrors)
    {
        $ordersWithLabels = new OrderCollection(Order::class, 'ordersWithOrderLabels');
        foreach ($orders as $order) {
            if (isset($labelErrors[$order->getId()])) {
                continue;
            }
            $ordersWithLabels->attach($order);
        }
        return $ordersWithLabels;
    }

    protected function removeZeroQuantityItemsFromOrders(OrderCollection $orders)
    {
        foreach ($orders as $order) {
            $items = $order->getItems();
            $nonZeroItems = new ItemCollection(
                Item::class,
                $items->getSourceDescription(),
                array_merge($items->getSourceFilters(), ['itemQuantityGreaterThan' => 0])
            );
            foreach ($items as $item) {
                if ($item->getItemQuantity() == 0) {
                    continue;
                }
                $nonZeroItems->attach($item);
            }
            $order->setItems($nonZeroItems);
        }
    }

    protected function ensureOrderItemsData(OrderCollection $orders, array $ordersItemsData, array $orderParcelsData)
    {
        // Each table row can be an item, a parcel or both (when there's only one item we collapse the item and parcel
        // into one row). In the latter case we end up with parcelData but not itemData. We'll rectify that if we can.
        foreach ($orders as $order) {
            if (isset($ordersItemsData[$order->getId()])) {
                continue;
            }
            $parcelData = (isset($orderParcelsData[$order->getId()]) ? $orderParcelsData[$order->getId()] : []); 
            if (count($order->getItems()) > 1 || count($parcelData) > 1) {
                continue;
            }
            $items = $order->getItems();
            $items->rewind();
            $item = $items->current();
            $ordersItemsData[$order->getId()][$item->getId()] = array_shift($parcelData);
        }
        return $ordersItemsData;
    }
}
