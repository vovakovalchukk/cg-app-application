<?php
namespace Orders\Courier\Label;

use CG\Account\Shared\Entity as Account;
use CG\Account\Shipping\Service as AccountService;
use CG\Channel\Shipping\Provider\Service\Repository as CarrierProviderServiceRepository;
use CG\Channel\Shipping\Services\Factory as ShippingServiceFactory;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\Http\StatusCode;
use CG\Locale\CountryNameByCode;
use CG\Locale\Length as LocaleLength;
use CG\Locale\Mass as LocaleMass;
use CG\Locking\Failure as LockingFailure;
use CG\Locking\Service as LockingService;
use CG\Order\Client\Label\Service as OrderLabelService;
use CG\Order\Client\Service as OrderService;
use CG\Order\Service\Filter as OrderFilter;
use CG\Order\Service\Tracking\Service as OrderTrackingService;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Courier\Label\OrderData;
use CG\Order\Shared\Courier\Label\OrderData\Collection as OrderDataCollection;
use CG\Order\Shared\Courier\Label\OrderItemsData;
use CG\Order\Shared\Courier\Label\OrderItemsData\Collection as OrderItemsDataCollection;
use CG\Order\Shared\Courier\Label\OrderItemsData\ItemData;
use CG\Order\Shared\Courier\Label\OrderItemsData\ItemData\Collection as ItemDataCollection;
use CG\Order\Shared\Courier\Label\OrderParcelsData;
use CG\Order\Shared\Courier\Label\OrderParcelsData\Collection as OrderParcelsDataCollection;
use CG\Order\Shared\Courier\Label\OrderParcelsData\ParcelData;
use CG\Order\Shared\Item\Entity as Item;
use CG\Order\Shared\Label\Entity as OrderLabel;
use CG\Order\Shared\Label\Filter as OrderLabelFilter;
use CG\Order\Shared\Label\Mapper as OrderLabelMapper;
use CG\Order\Shared\Label\Status as OrderLabelStatus;
use CG\Order\Shared\ShippableInterface as Order;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Product\Detail\Entity as ProductDetail;
use CG\Product\Detail\Mapper as ProductDetailMapper;
use CG\Product\Detail\Service as ProductDetailService;
use CG\Stdlib\DateTime as StdlibDateTime;
use CG\Stdlib\Exception\Runtime\Conflict;
use CG\Stdlib\Exception\Runtime\ValidationMessagesException;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stdlib\SanitizeTrait;
use CG\User\OrganisationUnit\Service as UserOUService;
use GearmanClient;
use Orders\Courier\GetProductDetailsForOrdersTrait;
use CG\Order\Shared\Collection as Orders;
use CG\Order\Shared\Label\Collection as OrderLabels;
use CG\Stdlib\Exception\Runtime\NotFound;

abstract class ServiceAbstract implements LoggerAwareInterface
{
    use LogTrait;
    use GetProductDetailsForOrdersTrait;
    use SanitizeTrait;

    const PDF_LABEL_DIR = '/tmp/dataplug-labels';
    const LABEL_MAX_ATTEMPTS = 10;
    const LABEL_ATTEMPT_INTERVAL_SEC = 1;
    const LABEL_SAVE_MAX_ATTEMPTS = 2;
    const PROD_DETAIL_SAVE_MAX_ATTEMPTS = 2;

    const LOG_CODE = 'OrderCourierLabelService';
    const LOG_PROD_DET_PERSIST = 'Looking for dimensions to save to ProductDetails';
    const LOG_PROD_DET_UPDATE = 'Updating ProductDetail for SKU %s, OU %d from data for Order %s';
    const LOG_PROD_DET_CREATE = 'Creating ProductDetail for SKU %s, OU %d from data for Order %s';
    const LOG_PROD_DET_ERROR = 'Failed to save ProductDetails for SKU %s, OU %d from data for Order %s. Will skip.';
    const LOG_CREATE_ORDER_LABEL = 'Creating OrderLabel for Order %s';
    const LOG_PDF_MERGE = 'Merging multiple label PDFs into one';
    const LOG_PDF_MERGE_WRITE_FAIL = 'Error writing PDF data to file';
    const LOG_PDF_MERGE_FAIL = 'Error merging PDF data';

    /** @var UserOUService */
    protected $userOUService;
    /** @var OrderService */
    protected $orderService;
    /** @var AccountService */
    protected $accountService;
    /** @var OrderLabelMapper */
    protected $orderLabelMapper;
    /** @var OrderLabelService */
    protected $orderLabelService;
    /** @var OrderTrackingService */
    protected $orderTrackingService;
    /** @var ProductDetailMapper */
    protected $productDetailMapper;
    /** @var ProductDetailService */
    protected $productDetailService;
    /** @var GearmanClient */
    protected $gearmanClient;
    /** @var CarrierProviderServiceRepository */
    protected $carrierProviderServiceRepo;
    /** @var ShippingServiceFactory */
    protected $shippingServiceFactory;
    /** @var LockingService */
    protected $lockingService;

    protected $orderLabelLocks = [];
    protected $productDetailFields = [
        'weight' => 'processWeightForProductDetails',
        'width'  => 'processDimensionForProductDetails',
        'height' => 'processDimensionForProductDetails',
        'length' => 'processDimensionForProductDetails',
        'harmonisedSystemCode' => null, // no processing necessary
        'countryOfOrigin' => 'processCountryOfOriginForProductDetails',
    ];
    protected const FIELD_TO_SETTER_MAP = [
        'harmonisedSystemCode' => 'setHsTariffNumber',
        'countryOfOrigin' => 'setCountryOfManufacture',
    ];
    protected const DATA_FIELD_TO_PRODUCT_DETAIL_FIELD_MAP = [
        'harmonisedSystemCode' => 'hsTariffNumber',
        'countryOfOrigin' => 'countryOfManufacture',
    ];

    public function __construct(
        UserOUService $userOuService,
        OrderService $orderService,
        AccountService $accountService,
        OrderLabelMapper $orderLabelMapper,
        OrderLabelService $orderLabelService,
        OrderTrackingService $orderTrackingService,
        ProductDetailMapper $productDetailMapper,
        ProductDetailService $productDetailService,
        GearmanClient $gearmanClient,
        CarrierProviderServiceRepository $carrierProviderServiceRepo,
        ShippingServiceFactory $shippingServiceFactory,
        LockingService $lockingService
    ) {
        $this->userOUService = $userOuService;
        $this->orderService = $orderService;
        $this->accountService = $accountService;
        $this->orderLabelMapper = $orderLabelMapper;
        $this->orderLabelService = $orderLabelService;
        $this->orderTrackingService = $orderTrackingService;
        $this->productDetailMapper = $productDetailMapper;
        $this->productDetailService = $productDetailService;
        $this->gearmanClient = $gearmanClient;
        $this->carrierProviderServiceRepo = $carrierProviderServiceRepo;
        $this->shippingServiceFactory = $shippingServiceFactory;
        $this->lockingService = $lockingService;
    }

    protected function getOrdersByIds(array $orderIds)
    {
        $filter = (new OrderFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setOrderIds($orderIds);
        return $this->orderService->fetchLinkedCollectionByFilter($filter);
    }

    protected function getOrderLabelForOrder(Order $order)
    {
        $orders = new OrderCollection(Order::class, __FUNCTION__);
        $orders->attach($order);
        $orderLabels = $this->getOrderLabelsForOrders($orders);
        $orderLabels->rewind();
        return $orderLabels->current();
    }

    protected function getOrderLabelsForOrders(OrderCollection $orders)
    {
        $labelStatuses = OrderLabelStatus::getAllStatuses();
        $labelStatusesNotCancelled = array_diff($labelStatuses, [OrderLabelStatus::CANCELLED]);
        $orderIds = $orders->getArrayOf('id');
        $filter = (new OrderLabelFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setOrderId($orderIds)
            ->setStatus($labelStatusesNotCancelled);
        return $this->orderLabelService->fetchCollectionByFilter($filter);
    }

    protected function getCarrierProviderService(Account $account)
    {
        return $this->carrierProviderServiceRepo->getProviderForAccount($account);
    }

    protected function persistProductDetailsForOrders(
        OrderCollection $orders,
        OrderParcelsDataCollection $orderParcelsData,
        OrderItemsDataCollection $ordersItemsData,
        OrganisationUnit $rootOu
    ) {
        $this->logDebug(__METHOD__, [], 'MYTEST');

        $this->logDebug(static::LOG_PROD_DET_PERSIST, [], static::LOG_CODE);
        $suitableOrders = new OrderCollection(Order::class, __FUNCTION__);
        foreach ($orders as $order) {
            // If there's multiple items and we don't have specific data for each then we don't know how the parcel data is made up
            if (count($order->getItems()) > 1 && !$ordersItemsData->containsId($order->getId())) {
                continue;
            }
            $suitableOrders->attach($order);
        }

//        $this->logDebugDump($orderParcelsData, 'ORDER PARCEL DATA', [], 'MYTEST');
//        $this->logDebugDump($ordersItemsData, 'ORDER ITEM DATA', [], 'MYTEST');

        $productDetails = $this->getProductDetailsForOrders($suitableOrders, $rootOu);
        foreach ($suitableOrders as $order) {
            /** @var OrderParcelsData $parcelsData */
            $parcelsData = ($orderParcelsData->containsId($order->getId()) ? $orderParcelsData->getById($order->getId()) : $this->getEmptyParcelDataForOrder($order));

//            if ($orderParcelsData->containsId($order->getId())) {
//                $this->logDebugDump($orderParcelsData->getById($order->getId()), 'ORDER PARCEL DATA', [], 'MYTEST');
//            }

//            $this->logDebugDump($parcelsData, 'Pracels DATA', [], 'MYTEST');

            /** @var OrderParcelsData $parcelsData */
            $parcelCount = count($parcelsData->getParcels());
            /** @var ParcelData $parcelData */
            $parcelData = (!empty($parcelsData) ? $parcelsData->getParcels()->getFirst() : null);
            /** @var OrderItemsData $itemsData */
            $itemsData = ($ordersItemsData->containsId($order->getId()) ? $ordersItemsData->getById($order->getId()) : null);

//            $this->logDebugDump($itemsData, 'Items DATA', [], 'MYTEST');

            $items = $order->getItems();
            foreach ($items as $item) {
                $productDetailData = ($itemsData && $itemsData->getItems()->containsId($item->getId()) ? $itemsData->getItems()->getById($item->getId())->toArray() : ($parcelData ? $parcelData->toArray() : []));
                $productDetailData = $this->copyDimensionsAndWeightToProductDetailData($productDetailData, $parcelData, $parcelCount);

                if ($itemsData && $itemsData->getItems()->containsId($item->getId())) {
                    $this->logDebugDump($itemsData->getItems()->getById($item->getId())->toArray(), 'Items DATA', [], 'MYTEST');
                }

                if ($parcelData) {
                    $this->logDebugDump($parcelData->toArray(), 'Pracel DATA', [], 'MYTEST');
                }

                $this->logDebugDump($productDetailData, 'productDetailData', [], 'MYTEST');

                $itemProductDetails = $productDetails->getBy('sku', $item->getItemSku());
                if (count($itemProductDetails) > 0) {
                    $itemProductDetails->rewind();
                    $itemProductDetail = $itemProductDetails->current();
                    $this->logDebug(static::LOG_PROD_DET_UPDATE, [$itemProductDetail->getSku(), $itemProductDetail->getOrganisationUnitId(), $order->getId()], static::LOG_CODE);
                    try {
                        $this->updateProductDetailFromInputData($itemProductDetail, $productDetailData, $item, $parcelCount);
                    } catch (\Exception $e) {
                        // We can live without product details, don't fail for this
                        $this->logException($e, 'error', __NAMESPACE__);
                        $this->logError(static::LOG_PROD_DET_ERROR, ['sku' => $itemProductDetail->getSku(), 'ou' => $itemProductDetail->getOrganisationUnitId(), 'order' => $order->getId()], [static::LOG_CODE, 'ProductDetailError']);
                    }
                } else {
                    $this->logDebug(static::LOG_PROD_DET_CREATE, [$item->getItemSku(), $rootOu->getId(), $order->getId()], static::LOG_CODE);
                    $productDetail = $this->createProductDetailFromInputData($productDetailData, $item, $parcelCount, $rootOu);
                    $productDetails->attach($productDetail);
                }
            }
        }
    }

    protected function copyDimensionsAndWeightToProductDetailData(array $productDetailData, ParcelData $parcelData, $parcelCount): array
    {
        if (!isset($productDetailData['weight']) || is_null($productDetailData['weight'])) {
            $productDetailData['weight'] = $parcelData ? $parcelData->toArray()['weight'] : null;
        }

//        if ($parcelCount <= 1) {
//            return $productDetailData;
//        }

        if (!isset($productDetailData['width']) || is_null($productDetailData['width'])) {
            $productDetailData['width'] = $parcelData ? $parcelData->toArray()['width'] : null;
        }
        if (!isset($productDetailData['width']) || is_null($productDetailData['width'])) {
            $productDetailData['height'] = $parcelData ? $parcelData->toArray()['height'] : null;
        }
        if (!isset($productDetailData['width']) || is_null($productDetailData['width'])) {
            $productDetailData['length'] = $parcelData ? $parcelData->toArray()['length'] : null;
        }

        return $productDetailData;
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

            if ($field == 'sku') {
                $value = $this->sanitizeSku($value);
            }

            $setter = static::FIELD_TO_SETTER_MAP[$field] ?? ('set' . ucfirst($field));
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
            return $this->updateProductDetailFromInputData($productDetail, $productDetailData, $item, $parcelCount, ++$attempt);
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
            $data[static::DATA_FIELD_TO_PRODUCT_DETAIL_FIELD_MAP[$field] ?? $field] = $value;
        }
        $productDetail = $this->productDetailMapper->fromArray($data);
        $hal = $this->productDetailService->save($productDetail);
        return $this->productDetailMapper->fromHal($hal);
    }

    protected function processWeightForProductDetails($value, Item $item, $parcelCount)
    {
        $displayUnit = LocaleMass::getForLocale($this->userOUService->getActiveUserContainer()->getLocale());
        return ProductDetail::convertMass($value / $item->getItemQuantity(), $displayUnit, ProductDetail::UNIT_MASS);
    }

    protected function processDimensionForProductDetails($value, Item $item, $parcelCount)
    {
        // Impossible to tell how to divide up dimensions
        if ($item->getItemQuantity() > 1 || $parcelCount > 1) {
            return null;
        }
        $displayUnit = LocaleLength::getForLocale($this->userOUService->getActiveUserContainer()->getLocale());
        return ProductDetail::convertLength($value, $displayUnit, ProductDetail::UNIT_LENGTH);
    }

    protected function processCountryOfOriginForProductDetails($value, Item $item, $parcelCount)
    {
        // check against iso codes, if not valid try and match via name
        if (CountryNameByCode::isValidCountryCode($value)) {
            return $value;
        }
        try {
            return CountryNameByCode::getCountryCodeFromName($value);
        } catch (\UnexpectedValueException $e) {
            return null;
        }
    }

    protected function createOrderLabelForOrder(
        Order $order,
        OrderData $orderData,
        OrderParcelsData $orderParcelsData,
        Account $shippingAccount
    ) {
        $this->logDebug(static::LOG_CREATE_ORDER_LABEL, [$order->getId()], static::LOG_CODE);

        $serviceName = $orderData->getServiceName();
        if (!$serviceName) {
            $services = $this->shippingServiceFactory->createShippingService($shippingAccount)->getShippingServicesForOrder($order);
            $serviceName = $services[$orderData->getService()] ?? $orderData->getService();
        }

        $date = new StdlibDateTime();
        $orderLabelData = [
            'organisationUnitId' => $order->getOrganisationUnitId(),
            'shippingAccountId' => $shippingAccount->getId(),
            'shippingServiceCode' => $orderData->getService(),
            'orderId' => $order->getId(),
            'status' => OrderLabelStatus::CREATING,
            'created' => $date->stdFormat(),
            'channelName' => $shippingAccount->getChannel(),
            'courierName' => $shippingAccount->getDisplayName(),
            'courierService' => (string)$serviceName,
            'insurance' => $orderData->getInsurance() ?? '',
            'insuranceMonetary' => $orderData->getInsuranceMonetary() ?? '',
            'signature' => $orderData->getSignature() ?? '',
            'deliveryInstructions' => $orderData->getDeliveryInstructions() ?? '',
            'parcels' => [],
        ];

        // If there's no parcels then add a default one
        if (count($orderParcelsData->getParcels()) == 0) {
            $orderParcelsData->getParcels()->attach(ParcelData::fromArray(['number' => 1]));
        }

        /** @var ParcelData $parcel */
        foreach ($orderParcelsData->getParcels() as $parcel) {
            $orderLabelData['parcels'][] = [
                'number' => $parcel->getNumber(),
                'weight' => $parcel->getWeight() ?? '',
                'width' => $parcel->getWidth() ?? '',
                'height' => $parcel->getHeight() ?? '',
                'length' => $parcel->getLength() ?? '',
            ];
        }
        $orderLabel = $this->orderLabelMapper->fromArray($orderLabelData);

        // Lock to prevent two people creating the same label at the same time
        try {
            $this->lockOrderLabel($orderLabel);
        } catch (LockingFailure $ex) {
            $this->logException($ex, 'error', __NAMESPACE__);
            $exception = new ValidationMessagesException('Locking error');
            $errorCode = StatusCode::LOCKED;
            $exception->addErrorWithField($order->getId().':'.StatusCode::LOCKED, 'Someone else appears to be creating that label');
            throw $exception;
        }

        // DO NOT save the OrderLabel at this stage. We only want to save them if the courier call is successful
        return $orderLabel;
    }

    protected function lockOrderLabel(OrderLabel $orderLabel)
    {
        $lock = $this->lockingService->lock($orderLabel);
        $this->orderLabelLocks[$orderLabel->getOrderId()] = $lock;
    }

    protected function unlockOrderLabel(OrderLabel $orderLabel)
    {
        if (isset($this->orderLabelLocks[$orderLabel->getOrderId()])) {
            $this->lockingService->unlock($this->orderLabelLocks[$orderLabel->getOrderId()]);
            unset($this->orderLabelLocks[$orderLabel->getOrderId()]);
        }
    }

    protected function unlockOrderLabels()
    {
        foreach ($this->orderLabelLocks as $lock) {
            $this->lockingService->unlock($lock);
        }
        $this->orderLabelLocks = [];
    }

    // Required by GetProductDetailsForOrdersTrait
    protected function getProductDetailService()
    {
        return $this->productDetailService;
    }

    protected function getEmptyParcelDataForOrder(Order $order): OrderParcelsData
    {
        return $this->getEmptyParcelDataForOrderId($order->getId());
    }

    protected function getEmptyParcelDataForOrderId(string $orderId): OrderParcelsData
    {
        return new OrderParcelsData($orderId, new OrderParcelsData\ParcelData\Collection());
    }

    protected function getOrCreateOrderLabelsForOrders(
        Orders $orders,
        OrderDataCollection $ordersData,
        OrderParcelsDataCollection $orderParcelsData,
        Account $shippingAccount
    ): OrderLabels {
        try {
            $orderLabels = $this->getOrderLabelsForOrders($orders);
        } catch (NotFound $exception) {
            $orderLabels = new OrderLabels(OrderLabel::class, __FUNCTION__, ['orderId' => $orders->getIds()]);
        }

        $missingOrders = array_diff($orders->getIds(), $orderLabels->getArrayOf('orderId'));
        if (empty($missingOrders)) {
            return $orderLabels;
        }

        foreach ($missingOrders as $missingOrderId) {
            $missingOrderParcelsData = ($orderParcelsData->containsId($missingOrderId)
                ? $orderParcelsData->getById($missingOrderId)
                : $this->getEmptyParcelDataForOrderId($missingOrderId)
            );
            $orderLabels->attach(
                $this->createOrderLabelForOrder(
                    $orders->getById($missingOrderId),
                    $ordersData->getById($missingOrderId),
                    $missingOrderParcelsData,
                    $shippingAccount
                )
            );
        }

        return $orderLabels;
    }

    protected function updateOrderLabelStatus(OrderLabels $orderLabels, string $status)
    {
        /** @var OrderLabel $orderLabel */
        foreach ($orderLabels as $orderLabel) {
            $this->logDebug(static::LOG_UPDATE, [$orderLabel->getOrderId()], static::LOG_CODE, ['order' => $orderLabel->getOrderId()]);
            $orderLabel->setStatus($status);
            $this->orderLabelService->save($orderLabel);
        }
    }
}