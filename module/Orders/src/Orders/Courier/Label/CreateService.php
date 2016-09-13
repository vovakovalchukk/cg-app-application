<?php
namespace Orders\Courier\Label;

use CG\Account\Shared\Entity as Account;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Item\Collection as ItemCollection;
use CG\Order\Shared\Item\Entity as Item;
use CG\Order\Shared\Label\Collection as OrderLabelCollection;
use CG\Order\Shared\Label\Entity as OrderLabel;
use CG\Order\Shared\Label\Status as OrderLabelStatus;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Product\Detail\Entity as ProductDetail;
use CG\Stdlib\DateTime as StdlibDateTime;
use CG\Stdlib\Exception\Runtime\Conflict;
use CG\Stdlib\Exception\Runtime\ValidationMessagesException;
use Orders\Courier\GetProductDetailsForOrdersTrait;

class CreateService extends ServiceAbstract
{
    use GetProductDetailsForOrdersTrait;

    const LABEL_MAX_ATTEMPTS = 10;
    const LABEL_ATTEMPT_INTERVAL_SEC = 1;
    const LABEL_SAVE_MAX_ATTEMPTS = 2;
    const PROD_DETAIL_SAVE_MAX_ATTEMPTS = 2;
    const LOG_CODE = 'OrderCourierLabelCreateService';
    const LOG_CREATE = 'Create label request for Order(s) %s, shipping Account %d';
    const LOG_CREATE_SEND = 'Sending create request to carrier provider for Order(s) %s, shipping Account %d';
    const LOG_CREATE_DONE = 'Completed create label request for Order(s) %s, shipping Account %d';
    const LOG_PROD_DET_PERSIST = 'Looking for dimensions to save to ProductDetails';
    const LOG_PROD_DET_UPDATE = 'Updating ProductDetail for SKU %s, OU %d from data for Order %s';
    const LOG_PROD_DET_CREATE = 'Creating ProductDetail for SKU %s, OU %d from data for Order %s';
    const LOG_GET_LABEL_ATTEMPT = 'Attempt %d to get label data for order number %s, Order %s, shipping Account %d';
    const LOG_GET_LABEL_RETRY = 'No label data found on this attempt, will retry for order number %s, Order %s, shipping Account %d. Giving up.';
    const LOG_GET_LABEL_FAILED = 'Max attempts (%d) to get label data reached for order number %s, Order %s, shipping Account %d. Giving up.';
    const LOG_GET_TRACKING = 'Looking for tracking numbers for order number %s, Order %s, shipping Account %d.';
    const LOG_GET_TRACKING_FOUND = 'Found tracking number %s for Order %s.';
    const LOG_GET_TRACKING_SAVE = 'Saving tracking numbers for Order %s.';
    const LOG_CREATE_ORDER_LABEL = 'Creating OrderLabel for Order %s';
    const LOG_UPDATE_ORDER_LABEL = 'Updating OrderLabel with PDF data for Order %s (attempt %d)';

    protected $productDetailFields = [
        'weight' => 'processWeightForProductDetails',
        'width'  => 'processDimensionForProductDetails',
        'height' => 'processDimensionForProductDetails',
        'length' => 'processDimensionForProductDetails',
    ];

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
        $shippingAccount = $this->accountService->fetch($shippingAccountId);
        $orders = $this->getOrdersByIds($orderIds);
        $this->removeZeroQuantityItemsFromOrders($orders);

        $this->persistProductDetailsForOrders($orders, $orderParcelsData, $ordersItemsData, $rootOu);
        $orderLabels = $this->createOrderLabelsForOrders($orders, $ordersData, $shippingAccount);
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
            $this->deleteOrderLabelsForFailedCreateAttempts($labelReadyStatuses, $orderLabels);
            $this->logDebug(static::LOG_CREATE_DONE, [$orderIdsString, $shippingAccountId], static::LOG_CODE);
            $this->removeGlobalLogEventParam('account')->removeGlobalLogEventParam('ou');

            return $labelReadyStatuses;
        } catch (\Exception $e) {
            // Remove labels so we don't get a label stuck in 'creating', preventing creation of new labels
            $this->removeOrderLabels($orderLabels);
            throw $e;
        }
    }

    protected function persistProductDetailsForOrders(
        OrderCollection $orders,
        array $orderParcelsData,
        array $ordersItemsData,
        OrganisationUnit $rootOu
    ) {
        $this->logDebug(static::LOG_PROD_DET_PERSIST, [], static::LOG_CODE);
        $suitableOrders = new OrderCollection(Order::class, __FUNCTION__);
        foreach ($orders as $order) {
            $parcelsData = (isset($orderParcelsData[$order->getId()]) ? $orderParcelsData[$order->getId()] : []);
            // If there's multiple items and we don't have specific data for each then we don't know how the parcel data is made up
            if (count($order->getItems()) > 1 && !isset($ordersItemsData[$order->getId()])) {
                continue;
            }
            $suitableOrders->attach($order);
        }

        $productDetails = $this->getProductDetailsForOrders($suitableOrders, $rootOu);
        foreach ($suitableOrders as $order) {
            $parcelsData = (isset($orderParcelsData[$order->getId()]) ? $orderParcelsData[$order->getId()] : []);
            $parcelCount = count($parcelsData);
            $parcelData = (!empty($parcelsData) ? array_pop($parcelsData) : []);
            $itemData = (isset($ordersItemsData[$order->getId()]) ? $ordersItemsData[$order->getId()] : []);
            $items = $order->getItems();
            foreach ($items as $item) {
                $productDetailData = (isset($itemData[$item->getId()]) ? $itemData[$item->getId()] : $parcelData);
                $itemProductDetails = $productDetails->getBy('sku', $item->getItemSku());
                if (count($itemProductDetails) > 0) {
                    $itemProductDetails->rewind();
                    $itemProductDetail = $itemProductDetails->current();
                    $this->logDebug(static::LOG_PROD_DET_UPDATE, [$itemProductDetail->getSku(), $itemProductDetail->getOrganisationUnitId(), $order->getId()], static::LOG_CODE);
                    $this->updateProductDetailFromInputData($itemProductDetail, $productDetailData, $item, $parcelCount);
                } else {
                    $this->logDebug(static::LOG_PROD_DET_CREATE, [$item->getItemSku(), $rootOu->getId(), $order->getId()], static::LOG_CODE);
                    $productDetail = $this->createProductDetailFromInputData($productDetailData, $item, $parcelCount, $rootOu);
                    $productDetails->attach($productDetail);
                }
            }
        }
    }

    protected function updateProductDetailFromInputData(
        ProductDetail $productDetail,
        array $productDetailData,
        Item $item,
        $parcelCount,
        $attempt = 1
    ) {
        $changes = false;
        foreach ($this->productDetailFields as $field => $callback) {
            if (!isset($productDetailData[$field]) || $productDetailData[$field] == '') {
                continue;
            }
            $value = ($callback ? $this->$callback($productDetailData[$field], $item, $parcelCount) : $productDetailData[$field]);
            if ($value === null) {
                continue;
            }
            $setter = 'set' . ucfirst($field);
            $productDetail->$setter($value);
            $changes = true;
        }
        if (!$changes) {
            return;
        }
        try {
            $this->productDetailService->save($productDetail);
        } catch (NotModified $e) {
            // No-op
        } catch (Conflict $e) {
            if ($attempt > static::PROD_DETAIL_SAVE_MAX_ATTEMPTS) {
                throw $e;
            }
            $productDetail = $this->productDetailService->fetch($productDetail->getId());
            return $this->updateProductDetailFromInputData($productDetail, $productDetailData, $item, $parcelCount, $attempt++);
        }
    }

    protected function createProductDetailFromInputData(
        array $productDetailData,
        Item $item,
        $parcelCount,
        OrganisationUnit $rootOu
    ) {
        $data = [
            'sku' => $item->getItemSku(),
            'organisationUnitId' => $rootOu->getId(),
        ];
        foreach ($this->productDetailFields as $field => $callback) {
            if (!isset($productDetailData[$field]) || $productDetailData[$field] == '') {
                continue;
            }
            $value = ($callback ? $this->$callback($productDetailData[$field], $item, $parcelCount) : $productDetailData[$field]);
            $data[$field] = $value;
        }
        $productDetail = $this->productDetailMapper->fromArray($data);
        $hal = $this->productDetailService->save($productDetail);
        return $this->productDetailMapper->fromHal($hal);
    }

    protected function processWeightForProductDetails($value, Item $item, $parcelCount)
    {
        return ProductDetail::convertMass($value / $item->getItemQuantity(), ProductDetail::DISPLAY_UNIT_MASS, ProductDetail::UNIT_MASS);
    }

    protected function processDimensionForProductDetails($value, Item $item, $parcelCount)
    {
        // Impossible to tell how to divide up dimensions
        if ($item->getItemQuantity() > 1 || $parcelCount > 1) {
            return null;
        }
        // Dimensions entered in centimetres but stored in metres
        return ProductDetail::convertLength($value, ProductDetail::DISPLAY_UNIT_LENGTH, ProductDetail::UNIT_LENGTH);
    }

    protected function createOrderLabelsForOrders(OrderCollection $orders, array $ordersData, Account $shippingAccount)
    {
        $orderLabels = new OrderLabelCollection(OrderLabel::class, __FUNCTION__, ['orderId' => $orders->getIds()]);
        foreach ($orders as $order) {
            $orderData = $ordersData[$order->getId()];
            $orderLabels->attach($this->createOrderLabelForOrder($order, $orderData, $shippingAccount));
        }
        return $orderLabels;
    }

    protected function createOrderLabelForOrder(Order $order, array $orderData, Account $shippingAccount)
    {
        $this->logDebug(static::LOG_CREATE_ORDER_LABEL, [$order->getId()], static::LOG_CODE);
        $date = new StdlibDateTime();
        $orderLabelData = [
            'organisationUnitId' => $order->getOrganisationUnitId(),
            'shippingAccountId' => $shippingAccount->getId(),
            'shippingServiceCode' => $orderData['service'],
            'orderId' => $order->getId(),
            'status' => OrderLabelStatus::CREATING,
            'created' => $date->stdFormat(),
        ];
        $orderLabel = $this->orderLabelMapper->fromArray($orderLabelData);
        return $this->orderLabelService->save($orderLabel);
    }

    protected function deleteOrderLabelsForFailedCreateAttempts(
        array $labelReadyStatuses,
        OrderLabelCollection $orderLabels
    ) {
        foreach ($labelReadyStatuses as $orderId => $status) {
            if (!($status instanceof ValidationMessagesException)) {
                continue;
            }
            $labelsByOrderId = $orderLabels->getBy('orderId', $orderId);
            $labelsByOrderId->rewind();
            $orderLabel = $labelsByOrderId->current();
            $this->orderLabelService->remove($orderLabel);
        }
        return $this;
    }

    protected function removeOrderLabels(OrderLabelCollection $orderLabels)
    {
        foreach ($orderLabels as $orderLabel) {
            $this->orderLabelService->remove($orderLabel);
        }
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

    // Required by GetProductDetailsForOrdersTrait
    protected function getProductDetailService()
    {
        return $this->productDetailService;
    }

}
