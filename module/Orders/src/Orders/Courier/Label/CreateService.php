<?php

namespace Orders\Courier\Label;

use CG\Account\Shared\Entity as Account;
use CG\Dataplug\Gearman\WorkerFunction\GetLabelData as GetLabelDataGF;
use CG\Dataplug\Gearman\Workload\GetLabelData as GetLabelDataWorkload;
use CG\Dataplug\Order\LabelMissingException;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Label\Entity as OrderLabel;
use CG\Order\Shared\Label\Status as OrderLabelStatus;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Product\Detail\Entity as ProductDetail;
use CG\Stdlib\DateTime as StdlibDateTime;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\User\Entity as User;
use Orders\Courier\GetProductDetailsForOrdersTrait;

class CreateService extends ServiceAbstract
{

    use GetProductDetailsForOrdersTrait;

    const LABEL_MAX_ATTEMPTS = 10;
    const LABEL_ATTEMPT_INTERVAL_SEC = 1;
    const LABEL_SAVE_MAX_ATTEMPTS = 2;
    const CM_PER_M = 100;
    const LOG_CODE = 'OrderCourierLabelCreateService';
    const LOG_CREATE = 'Create label request for Order(s) %s, shipping Account %d';
    const LOG_CREATE_SEND = 'Sending create request to Dataplug for Order(s) %s, shipping Account %d';
    const LOG_CREATE_UNEXPECTED_RESPONSE = 'Dataplug response from create request missing Order nodes for Order(s) %s, shipping Account %d';
    const LOG_CREATE_MISSING_REF = 'Dataplug response from create request missing Order->OrderNumber for one or more Orders (of set: %s), shipping Account %d';
    const LOG_CREATE_REF = 'Successfully created order(s) with Dataplug and got order number(s) %s for Orders %s, shipping Account %d';
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
    const LOG_GET = 'Getting order details from Dataplug for reference %s, shipping Account %d';
    const LOG_GET_MISSING_ITEMS = 'Dataplug response from retrieve request missing parcel data for Order %s';
    const LOG_GET_MISSING_LABEL = 'Dataplug response from retrieve request missing label data for Order %s';
    const LOG_CREATE_ORDER_LABEL = 'Creating OrderLabel for Order %s';
    const LOG_UPDATE_ORDER_LABEL = 'Updating OrderLabel with PDF data for Order %s (attempt %d)';

    protected $productDimensionFields = ['weight', 'width', 'height', 'length'];

    public function createForOrdersData(array $orderIds, array $ordersData, array $orderParcelsData, $shippingAccountId)
    {
        $orderIdsString = implode(',', $orderIds);
        $rootOu = $this->userOUService->getRootOuByActiveUser();
        $user = $this->userOUService->getActiveUser();
        $this->addGlobalLogEventParam('account', $shippingAccountId)->addGlobalLogEventParam('ou', $rootOu->getId());
        $this->logDebug(static::LOG_CREATE, [$orderIdsString, $shippingAccountId], static::LOG_CODE);
        $shippingAccount = $this->accountService->fetch($shippingAccountId);
        $orders = $this->getOrdersByIds($orderIds);
        $this->persistDimensionsForOrders($orders, $orderParcelsData, $rootOu);
        $orderLabels = $this->createOrderLabelsForOrders($orders);
        $request = $this->mapper->ordersAndDataToDataplugCreateRequest($orders, $ordersData, $orderParcelsData, $rootOu);
        $this->logDebug(static::LOG_CREATE_SEND, [$orderIdsString, $shippingAccountId], static::LOG_CODE);
        $response = $this->dataplugClient->sendRequest($request, $shippingAccount);
        if (!isset($response->Order)) {
            throw new RuntimeException(vsprintf(static::LOG_CREATE_UNEXPECTED_RESPONSE, [$orderIdsString, $shippingAccountId]));
        }
        $orderNumbers = [];
        foreach ($response->Order as $responseOrder) {
            if (!isset($responseOrder->OrderNumber, $responseOrder->Reference)) {
                throw new RuntimeException(vsprintf(static::LOG_CREATE_MISSING_REF, [$orderIdsString, $shippingAccountId]));
            }
            $orderNumbers[(string)$responseOrder->Reference] = (string)$responseOrder->OrderNumber;
        }
        $this->logDebug(static::LOG_CREATE_REF, [implode(',', $orderNumbers), $orderIdsString, $shippingAccountId], static::LOG_CODE);

        $labelReadyStatuses = [];
        foreach ($orders as $order) {
            $orderNumber = $orderNumbers[$order->getId()];
            $orderLabel = $orderLabels[$order->getId()];
            $success = $this->getAndProcessDataplugOrderDetails(
                $order, $shippingAccount, $orderNumber, $orderLabel, $user
            );
            $labelReadyStatuses[$order->getId()] = $success;
        }
        $this->logDebug(static::LOG_CREATE_DONE, [$orderIdsString, $shippingAccountId], static::LOG_CODE);
        $this->removeGlobalLogEventParam('account')->removeGlobalLogEventParam('ou');
        return $labelReadyStatuses;
    }

    protected function persistDimensionsForOrders(OrderCollection $orders, array $orderParcelsData, OrganisationUnit $rootOu)
    {
        $this->logDebug(static::LOG_PROD_DET_PERSIST, [], static::LOG_CODE);
        $suitableOrders = new OrderCollection(Order::class, __FUNCTION__);
        foreach ($orders as $order) {
            $parcelsData = $orderParcelsData[$order->getId()];
            // if there's multiple items or parcels we won't know which the dimensions relate to directly
            if (count($order->getItems()) > 1 || count($parcelsData) > 1) {
                continue;
            }
            $suitableOrders->attach($order);
        }

        $productDetails = $this->getProductDetailsForOrders($suitableOrders, $rootOu);
        foreach ($suitableOrders as $order) {
            $parcelsData = $orderParcelsData[$order->getId()];
            $parcelData = array_pop($parcelsData);
            $items = $order->getItems();
            $items->rewind();
            $item = $items->current();
            $itemProductDetails = $productDetails->getBy('sku', $item->getItemSku());
            if (count($itemProductDetails) > 0) {
                $itemProductDetails->rewind();
                $itemProductDetail = $itemProductDetails->current();
                $this->logDebug(static::LOG_PROD_DET_UPDATE, [$itemProductDetail->getSku(), $itemProductDetail->getOrganisationUnitId(), $order->getId()], static::LOG_CODE);
                $this->updateProductDetailFromParcelData($itemProductDetail, $parcelData);
            } else {
                $this->logDebug(static::LOG_PROD_DET_CREATE, [$item->getItemSku(), $rootOu->getId(), $order->getId()], static::LOG_CODE);
                $productDetail = $this->createProductDetailFromParcelData($parcelData, $item->getItemSku(), $rootOu);
                $productDetails->attach($productDetail);
            }
        }
    }

    protected function updateProductDetailFromParcelData(ProductDetail $productDetail, array $parcelData)
    {
        $changes = false;
        foreach ($this->productDimensionFields as $field) {
            if (!isset($parcelData[$field]) || $parcelData[$field] == '') {
                continue;
            }
            $value = $this->getProductDetailValueFromParcelData($field, $parcelData);
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
        }
    }

    protected function createProductDetailFromParcelData(array $parcelData, $sku, OrganisationUnit $rootOu)
    {
        $productDetailData = [
            'sku' => $sku,
            'organisationUnitId' => $rootOu->getId(),
        ];
        foreach ($this->productDimensionFields as $field) {
            if (!isset($parcelData[$field]) || $parcelData[$field] == '') {
                continue;
            }
            $value = $this->getProductDetailValueFromParcelData($field, $parcelData);
            $productDetailData[$field] = $value;
        }
        $productDetail = $this->productDetailMapper->fromArray($productDetailData);
        $hal = $this->productDetailService->save($productDetail);
        return $this->productDetailMapper->fromHal($hal);
    }

    protected function getProductDetailValueFromParcelData($field, array $parcelData)
    {
        $value = $parcelData[$field];
        if ($field != 'weight') {
            // Dimensions entered in centimetres but stored in metres
            $value /= static::CM_PER_M;
        }
        return $value;
    }

    protected function createOrderLabelsForOrders(OrderCollection $orders)
    {
        $orderLabels = [];
        foreach ($orders as $order) {
            $orderLabels[$order->getId()] = $this->createOrderLabelForOrder($order);
        }
        return $orderLabels;
    }

    protected function createOrderLabelForOrder(Order $order)
    {
        $this->logDebug(static::LOG_CREATE_ORDER_LABEL, [$order->getId()], static::LOG_CODE);
        $date = new StdlibDateTime();
        $orderLabelData = [
            'organisationUnitId' => $order->getOrganisationUnitId(),
            'orderId' => $order->getId(),
            'status' => OrderLabelStatus::CREATING,
            'created' => $date->stdFormat(),
        ];
        $orderLabel = $this->orderLabelMapper->fromArray($orderLabelData);
        $hal = $this->orderLabelService->save($orderLabel);
        return $this->orderLabelMapper->fromHal($hal);
    }

    protected function getAndProcessDataplugOrderDetails(
        Order $order,
        Account $shippingAccount,
        $orderNumber,
        OrderLabel $orderLabel,
        User $user
    ) {
        try {
            $this->dataplugOrderService->getAndProcessDataplugOrderDetails(
                $order, $shippingAccount, $orderNumber, $orderLabel, $user
            );
            return true;
        } catch (LabelMissingException $e) {
            $this->createJobToGetAndProcessDataplugOrderDetails(
                $order, $shippingAccount, $orderNumber, $orderLabel, $user
            );
            return false;
        } catch (StorageException $e) {
            // Remove label so we don't get a label stuck in 'creating', preventing creation of new labels
            $this->orderLabelService->remove($orderLabel);
            throw $e;
        }
    }

    protected function createJobToGetAndProcessDataplugOrderDetails(
        Order $order,
        Account $shippingAccount,
        $orderNumber,
        OrderLabel $orderLabel,
        User $user
    ) {
        $workload = new GetLabelDataWorkload(
            $order->getId(), $shippingAccount->getId(), $orderLabel->getId(), $user->getId(), $orderNumber
        );
        $handle = GetLabelDataGF::FUNCTION_NAME . '-' . $order->getId();
        $this->gearmanClient->doBackground(GetLabelDataGF::FUNCTION_NAME, serialize($workload), $handle);
    }

    // Required by GetProductDetailsForOrdersTrait
    protected function getProductDetailService()
    {
        return $this->productDetailService;
    }

}
