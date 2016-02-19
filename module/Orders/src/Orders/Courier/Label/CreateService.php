<?php
namespace Orders\Courier\Label;

use CG\Account\Shared\Entity as Account;
use CG\Dataplug\Carrier\Entity as Carrier;
use CG\Dataplug\Gearman\WorkerFunction\GetLabelData as GetLabelDataGF;
use CG\Dataplug\Gearman\Workload\GetLabelData as GetLabelDataWorkload;
use CG\Dataplug\Order\LabelMissingException;
use CG\Dataplug\Request\CreateOrders as DataplugCreateRequest;
use CG\Dataplug\Request\RetrieveOrders as DataplugRetrieveRequest;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Label\Collection as OrderLabelCollection;
use CG\Order\Shared\Label\Entity as OrderLabel;
use CG\Order\Shared\Label\Status as OrderLabelStatus;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Product\Detail\Entity as ProductDetail;
use CG\Stdlib\DateTime as StdlibDateTime;
use CG\Stdlib\Exception\Runtime\ValidationMessagesException;
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

    protected $productDimensionFields = ['weight', 'width', 'height', 'length'];

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

        $this->persistDimensionsForOrders($orders, $orderParcelsData, $ordersItemsData, $rootOu);
        $orderLabels = $this->createOrderLabelsForOrders($orders, $shippingAccount);

        $this->logDebug(static::LOG_CREATE_SEND, [$orderIdsString, $shippingAccountId], static::LOG_CODE);
        $labelReadyStatuses = $this->carrierProviderService->createLabelsForOrders(
            $orders,
            $orderLabels,
            $ordersData,
            $orderParcelsData,
            $rootOu,
            $shippingAccount,
            $user
        );
        $this->logDebug(static::LOG_CREATE_DONE, [$orderIdsString, $shippingAccountId], static::LOG_CODE);
        $this->removeGlobalLogEventParam('account')->removeGlobalLogEventParam('ou');

        return $labelReadyStatuses;
    }

    protected function persistDimensionsForOrders(
        OrderCollection $orders,
        array $orderParcelsData,
        array $ordersItemsData,
        OrganisationUnit $rootOu
    ) {
        $this->logDebug(static::LOG_PROD_DET_PERSIST, [], static::LOG_CODE);
        $suitableOrders = new OrderCollection(Order::class, __FUNCTION__);
        foreach ($orders as $order) {
            $parcelsData = $orderParcelsData[$order->getId()];
            // if there's multiple parcels we won't know which products the dimensions relate to directly
            if (count($parcelsData) > 1) {
                continue;
            }
            // If there's multiple items and we don't have specific data for each then we don't know how the parcel data is made up
            if (count($order->getItems()) > 1 && !isset($ordersItemsData[$order->getId()])) {
                continue;
            }
            $suitableOrders->attach($order);
        }

        $productDetails = $this->getProductDetailsForOrders($suitableOrders, $rootOu);
        foreach ($suitableOrders as $order) {
            $parcelsData = $orderParcelsData[$order->getId()];
            $parcelData = array_pop($parcelsData);
            $itemData = (isset($ordersItemsData[$order->getId()]) ? $ordersItemsData[$order->getId()] : []);
            $items = $order->getItems();
            foreach ($items as $item) {
                if ($item->getItemQuantity() > 1) {
                    continue;
                }
                $itemDimensionData = (isset($itemData[$item->getId()]) ? $itemData[$item->getId()] : $parcelData);
                $itemProductDetails = $productDetails->getBy('sku', $item->getItemSku());
                if (count($itemProductDetails) > 0) {
                    $itemProductDetails->rewind();
                    $itemProductDetail = $itemProductDetails->current();
                    $this->logDebug(static::LOG_PROD_DET_UPDATE, [$itemProductDetail->getSku(), $itemProductDetail->getOrganisationUnitId(), $order->getId()], static::LOG_CODE);
                    $this->updateProductDetailFromInputDimensionData($itemProductDetail, $itemDimensionData);
                } else {
                    $this->logDebug(static::LOG_PROD_DET_CREATE, [$item->getItemSku(), $rootOu->getId(), $order->getId()], static::LOG_CODE);
                    $productDetail = $this->createProductDetailFromInputDimensionData($itemDimensionData, $item->getItemSku(), $rootOu);
                    $productDetails->attach($productDetail);
                }
            }
        }
    }

    protected function updateProductDetailFromInputDimensionData(ProductDetail $productDetail, array $itemDimensionData)
    {
        $changes = false;
        foreach ($this->productDimensionFields as $field) {
            if (!isset($itemDimensionData[$field]) || $itemDimensionData[$field] == '') {
                continue;
            }
            $value = $this->getProductDetailValueFromInputDimensionData($field, $itemDimensionData);
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

    protected function createProductDetailFromInputDimensionData(array $itemDimensionData, $sku, OrganisationUnit $rootOu)
    {
        $productDetailData = [
            'sku' => $sku,
            'organisationUnitId' => $rootOu->getId(),
        ];
        foreach ($this->productDimensionFields as $field) {
            if (!isset($itemDimensionData[$field]) || $itemDimensionData[$field] == '') {
                continue;
            }
            $value = $this->getProductDetailValueFromInputDimensionData($field, $itemDimensionData);
            $productDetailData[$field] = $value;
        }
        $productDetail = $this->productDetailMapper->fromArray($productDetailData);
        $hal = $this->productDetailService->save($productDetail);
        return $this->productDetailMapper->fromHal($hal);
    }

    protected function getProductDetailValueFromInputDimensionData($field, array $itemDimensionData)
    {
        $value = $itemDimensionData[$field];
        if ($field != 'weight') {
            // Dimensions entered in centimetres but stored in metres
            $value /= static::CM_PER_M;
        }
        return $value;
    }

    protected function createOrderLabelsForOrders(OrderCollection $orders, Account $shippingAccount)
    {
        $orderLabels = new OrderLabelCollection(OrderLabel::class, __FUNCTION__, ['orderId' => $orders->getIds()]);
        foreach ($orders as $order) {
            $orderLabels->attach($this->createOrderLabelForOrder($order, $shippingAccount));
        }
        return $orderLabels;
    }

    protected function createOrderLabelForOrder(Order $order, Account $shippingAccount)
    {
        $this->logDebug(static::LOG_CREATE_ORDER_LABEL, [$order->getId()], static::LOG_CODE);
        $date = new StdlibDateTime();
        $orderLabelData = [
            'organisationUnitId' => $order->getOrganisationUnitId(),
            'shippingAccountId' => $shippingAccount->getId(),
            'orderId' => $order->getId(),
            'status' => OrderLabelStatus::CREATING,
            'created' => $date->stdFormat(),
        ];
        $orderLabel = $this->orderLabelMapper->fromArray($orderLabelData);
        $hal = $this->orderLabelService->save($orderLabel);
        return $this->orderLabelMapper->fromHal($hal);
    }

    // Required by GetProductDetailsForOrdersTrait
    protected function getProductDetailService()
    {
        return $this->productDetailService;
    }

}