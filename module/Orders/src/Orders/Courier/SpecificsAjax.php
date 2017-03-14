<?php
namespace Orders\Courier;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Entity as Account;
use CG\Channel\Shipping\Provider\Service\CancelInterface as CarrierServiceProviderCancelInterface;
use CG\Channel\Shipping\Provider\Service\Repository as CarrierServiceProviderRepository;
use CG\Channel\Shipping\Services\Factory as ShippingServiceFactory;
use CG\Order\Client\Service as OrderService;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Item\Collection as ItemCollection;
use CG\Order\Shared\Item\Entity as Item;
use CG\Order\Shared\Label\Collection as OrderLabelCollection;
use CG\Order\Shared\Label\Entity as OrderLabel;
use CG\Order\Shared\Label\Filter as OrderLabelFilter;
use CG\Order\Shared\Label\Status as OrderLabelStatus;
use CG\Order\Shared\Label\StorageInterface as OrderLabelStorage;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Product\Collection as ProductCollection;
use CG\Product\Detail\Collection as ProductDetailCollection;
use CG\Product\Detail\Entity as ProductDetail;
use CG\Product\Detail\Service as ProductDetailService;
use CG\Stdlib\DateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\OrganisationUnit\Service as UserOUService;
use DateTimeZone;
use Orders\Courier\GetProductDetailsForOrdersTrait;
use Orders\Courier\Service;

class SpecificsAjax
{
    use GetProductDetailsForOrdersTrait;

    const DEFAULT_PARCELS = 1;
    const MIN_PARCELS = 1;
    const MAX_PARCELS = 10;

    /** @var OrderService */
    protected $orderService;
    /** @var AccountService */
    protected $accountService;
    /** @var ShippingServiceFactory */
    protected $shippingServiceFactory;
    /** @var UserOUService */
    protected $userOuService;
    /** @var OrderLabelStorage */
    protected $orderLabelStorage;
    /** @var CarrierServiceProviderRepository */
    protected $carrierServiceProviderRepository;
    /** @var ProductDetailService */
    protected $productDetailService;
    /** @var Service */
    protected $courierService;

    protected $specificsListRequiredOrderFields = ['parcels', 'collectionDate', 'collectionTime'];
    protected $specificsListRequiredParcelFields = ['weight', 'width', 'height', 'length', 'packageType', 'itemParcelAssignment'];

    public function __construct(
        OrderService $orderService,
        AccountService $accountService,
        ShippingServiceFactory $shippingServiceFactory,
        UserOUService $userOuService,
        OrderLabelStorage $orderLabelStorage,
        CarrierServiceProviderRepository $carrierServiceProviderRepository,
        ProductDetailService $productDetailService,
        Service $courierService
    ) {
        $this->orderService = $orderService;
        $this->accountService = $accountService;
        $this->shippingServiceFactory = $shippingServiceFactory;
        $this->userOuService = $userOuService;
        $this->orderLabelStorage = $orderLabelStorage;
        $this->carrierServiceProviderRepository = $carrierServiceProviderRepository;
        $this->productDetailService = $productDetailService;
        $this->courierService = $courierService;
    }

    /**
     * @return array
     */
    public function getSpecificsListData(array $orderIds, $courierAccountId, array $ordersData, array $ordersParcelsData)
    {
        $orders = $this->courierService->fetchOrdersById($orderIds);
        $this->courierService->removeZeroQuantityItemsFromOrders($orders);
        $courierAccount = $this->accountService->fetch($courierAccountId);
        $data = $this->formatOrdersAsSpecificsListData($orders, $courierAccount, $ordersData, $ordersParcelsData);
        return $this->sortSpecificsListData($data, $courierAccount);
    }

    protected function formatOrdersAsSpecificsListData(
        OrderCollection $orders,
        Account $courierAccount,
        array $ordersData,
        array $ordersParcelsData
    ) {
        $data = [];
        $rootOu = $this->userOuService->getRootOuByActiveUser();
        $products = $this->courierService->getProductsForOrders($orders, $rootOu);
        $productDetails = $this->getProductDetailsForOrders($orders, $rootOu);
        $labels = $this->getOrderLabelsForOrders($orders);
        $carrierOptions = $this->courierService->getCarrierOptions($courierAccount);
        foreach ($orders as $order) {
            $orderData = $this->courierService->getCommonOrderListData($order, $rootOu);
            unset($orderData['courier']);
            $orderLabel = null;
            $orderLabels = $labels->getBy('orderId', $order->getId());
            if (count($orderLabels) > 0) {
                $orderLabels->rewind();
                $orderLabel = $orderLabels->current();
            }
            $inputData = (isset($ordersData[$order->getId()]) ? $ordersData[$order->getId()] : []);
            $options = $this->courierService->getCarrierOptions($courierAccount, (isset($inputData['service']) ? $inputData['service'] : null));
            $specificsOrderData = $this->getSpecificsOrderListDataDefaults($order, $courierAccount, $options, $orderLabel);
            $parcelsInputData = (isset($ordersParcelsData[$order->getId()]) ? $ordersParcelsData[$order->getId()] : []);
            if (isset($inputData['service']) && $inputData['service'] === "") {
                unset($inputData['service']);
            }
            $orderData = array_merge($orderData, $specificsOrderData, $inputData);
            $orderData = $this->checkOrderDataParcels($orderData, $parcelsInputData, $order);
            $itemsData = $this->formatOrderItemsAsSpecificsListData($order->getItems(), $orderData, $products, $productDetails, $options, $carrierOptions);
            $parcelsData = $this->getParcelOrderListData($order, $orderData, $parcelsInputData, $options, $carrierOptions);
            foreach ($parcelsData as $parcelData) {
                array_push($itemsData, $parcelData);
            }
            $data = array_merge($data, $itemsData);
        }
        $now = (new DateTime())->setTimezone(new DateTimeZone($this->getUsersTimezone()));
        foreach ($data as &$row) {
            $row['currentTime'] = $now->uiTimeFormat();
            $row['timezone'] = $now->getTimezone()->getName();
        }
        $data = $this->performSumsOnSpecificsListData($data, $options);
        $data = $this->courierService->getCarrierOptionsProvider($courierAccount)
            ->addCarrierSpecificDataToListArray($data, $courierAccount);
        return $data;
    }

    protected function getUsersTimezone()
    {
        /** @var OrganisationUnit $rootOu */
        $rootOu = $this->userOuService->getRootOuByActiveUser();
        return $rootOu->getTimezone();
    }

    protected function getSpecificsOrderListDataDefaults(
        Order $order,
        Account $courierAccount,
        array $options,
        OrderLabel $orderLabel = null
    ) {
        $services = $this->shippingServiceFactory->createShippingService($courierAccount)->getShippingServicesForOrder($order);
        $providerService = $this->getCarrierServiceProvider($courierAccount);
        $cancellable = ($providerService instanceof CarrierServiceProviderCancelInterface &&
            $providerService->isCancellationAllowedForOrder($courierAccount, $order));
        $data = [
            'parcels' => static::DEFAULT_PARCELS,
            // The order row will always be parcel 1, only parcel rows might be other numbers
            'parcelNumber' => 1,
            'labelStatus' => ($orderLabel ? $orderLabel->getStatus() : ''),
            'cancellable' => $cancellable,
            'services' => $services,
        ];
        foreach ($options as $option) {
            $data[$option] = '';
            if ($option == 'collectionDate') {
                $data[$option] = date('Y-m-d');
            }
        }
        return $data;
    }

    protected function checkOrderDataParcels(array $orderData, array $parcelsData, Order $order)
    {
        if ($orderData['parcels'] < static::MIN_PARCELS) {
            $orderData['parcels'] = static::MIN_PARCELS;
        } elseif ($orderData['parcels'] > static::MAX_PARCELS) {
            $orderData['parcels'] = static::MAX_PARCELS;
        }
        // Mustache is logic-less so any logic, however basic, has to be done here
        $singleRow = ($orderData['parcels'] == 1 && count($order->getItems()) == 1);
        $orderData['parcelRow'] = $singleRow;
        $orderData['actionRow'] = $singleRow;
        if ($singleRow && !empty($parcelsData)) {
            $singleParcelData = array_shift($parcelsData);
            $orderData = array_merge($orderData, $singleParcelData);
        }
        return $orderData;
    }

    /**
     * @return OrderLabelCollection
     */
    protected function getOrderLabelsForOrders(OrderCollection $orders)
    {
        $orderIds = [];
        foreach ($orders as $order) {
            $orderIds[] = $order->getId();
        }
        $labelStatuses = OrderLabelStatus::getAllStatuses();
        $labelStatusesNotCancelled = array_diff($labelStatuses, [OrderLabelStatus::CANCELLED]);
        $filter = (new OrderLabelFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setOrderId($orderIds)
            ->setStatus($labelStatusesNotCancelled);
        try {
            $orderLabels = $this->orderLabelStorage->fetchCollectionByFilter($filter);
        } catch (NotFound $e) {
            $orderLabels = new OrderLabelCollection(OrderLabel::class, 'empty');
        }
        return $orderLabels;
    }

    protected function formatOrderItemsAsSpecificsListData(
        ItemCollection $items,
        array $orderData,
        ProductCollection $products,
        ProductDetailCollection $productDetails,
        array $options,
        array $carrierOptions
    ) {
        $itemsData = [];
        $itemCount = 0;
        foreach ($items as $item) {
            $rowData = null;
            if ($itemCount == 0) {
                $rowData = $orderData;
            }
            $itemData = $this->courierService->getCommonItemListData($item, $products, $rowData);
            $specificsItemData = $this->getSpecificsItemListData($item, $productDetails, $options, $rowData);
            $specificsItemData['itemRow'] = true;
            $specificsItemData['showWeight'] = true;
            $specificsItemData['labelStatus'] = $orderData['labelStatus'];
            $specificsItemData['requiredFields'] = $this->getFieldsRequirementStatus($options, $carrierOptions);
            $itemsData[] = array_merge($itemData, $specificsItemData);
            $itemCount++;
        }
        return $itemsData;
    }

    protected function getSpecificsItemListData(
        Item $item,
        ProductDetailCollection $productDetails,
        array $options,
        array $rowData = null
    ) {
        $data = ($rowData ?: []);
        $productDetail = null;
        $matchingProductDetails = $productDetails->getBy('sku', $item->getItemSku());
        if (count($matchingProductDetails) > 0) {
            $matchingProductDetails->rewind();
            $productDetail = $matchingProductDetails->current();
        }

        foreach ($options as $option) {
            if (isset($data[$option]) && $data[$option] != '') {
                continue;
            }
            $data[$option] = '';
        }

        if (!($productDetail instanceof ProductDetail)) {
            return $data;
        }

        // Always add all product details even if there's no option for them as sometimes they're used indirectly
        $data['weight'] = $this->processWeightFromProductDetails($productDetail->getDisplayWeight(), $item);
        $data['width'] = $this->processDimensionFromProductDetails($productDetail->getDisplayWidth(), $item);
        $data['height'] = $this->processDimensionFromProductDetails($productDetail->getDisplayHeight(), $item);
        $data['length'] = $this->processDimensionFromProductDetails($productDetail->getDisplayLength(), $item);

        return $data;
    }

    protected function processWeightFromProductDetails($value, Item $item)
    {
        if ($value === null) {
            return '';
        }
        return $value * $item->getItemQuantity();
    }

    protected function processDimensionFromProductDetails($value, Item $item)
    {
        // Impossible to tell how to multiply dimensions
        if ($value === null || $item->getItemQuantity() > 1) {
            return '';
        }
        return $value;
    }

    protected function getFieldsRequirementStatus(array $serviceOptions, array $carrierOptions)
    {
        $fieldsRequiredStatus = [];
        $notRequiredFields = array_diff($carrierOptions, $serviceOptions);
        $notRequiredFieldsKeyed = array_flip($notRequiredFields);
        $requiredFields = array_merge($this->specificsListRequiredOrderFields, $this->specificsListRequiredParcelFields);
        $requiredFieldsKeyed = array_flip($requiredFields);

        foreach ($carrierOptions as $option) {
            $fieldsRequiredStatus[$option] = [
                'show' => (!isset($notRequiredFieldsKeyed[$option])),
                'required' => (isset($requiredFieldsKeyed[$option])),
            ];
        }

        return $fieldsRequiredStatus;
    }

    protected function getParcelOrderListData(
        Order $order,
        array $orderData,
        array $parcelsInputData,
        array $options,
        array $carrierOptions
    ) {
        $parcels = $orderData['parcels'];
        if (count($order->getItems()) <= 1 && $parcels <= 1) {
            return [];
        }

        $parcelsData = [];
        for ($parcel = 1; $parcel <= $parcels; $parcel++) {
            $parcelData = $this->courierService->getChildRowListData($order->getId(), $parcel);
            $parcelData['parcelNumber'] = $parcel;
            $parcelData['parcelRow'] = true;
            $parcelData['showWeight'] = true;
            $parcelData['actionRow'] = ($parcel == $parcels);
            $parcelData['labelStatus'] = $orderData['labelStatus'];
            $parcelData['serviceOptions'] = $orderData['serviceOptions'];
            $parcelData['cancellable'] = $orderData['cancellable'];
            $parcelData['shippingCountryCode'] = $orderData['shippingCountryCode'];
            $parcelData['itemImageText'] = 'Package ' . $parcel;
            $parcelData['requiredFields'] = $this->getFieldsRequirementStatus($options, $carrierOptions);
            foreach ($options as $option) {
                $parcelData[$option] = (isset($orderData[$option]) ? $orderData[$option] : '');
            }

            if (isset($parcelsInputData[$parcel])) {
                $parcelData = array_merge($parcelData, $parcelsInputData[$parcel]);
            }

            $parcelsData[] = $parcelData;
        }
        return $parcelsData;
    }

    protected function performSumsOnSpecificsListData(array $data, array $options)
    {
        $optionsKeyed = array_flip($options);
        if (isset($optionsKeyed['weight'])) {
            $data = $this->sumParcelWeightsOnSpecificsListData($data);
        }
        return $data;
    }

    protected function sumParcelWeightsOnSpecificsListData(array $data)
    {
        $orderRows = $this->courierService->groupListDataByOrder($data);
        foreach ($orderRows as &$rows) {
            $itemCount = 0;
            $parcelCount = 0;
            $weightSum = 0;
            foreach ($rows as &$row) {
                if (isset($row['itemRow']) && $row['itemRow']) {
                    $itemCount++;
                    if (isset($row['weight'])) {
                        $weightSum += (float)$row['weight'];
                    }
                }
                if (isset($row['parcelRow']) && $row['parcelRow']) {
                    $parcelCount++;
                }
            }
            if ($itemCount > 1 && $parcelCount == 1 && $weightSum > 0) {
                // The parcel row will be the last one we looked at so we can re-use $row
                $row['weight'] = $weightSum;
            }
        }

        $data = [];
        foreach ($orderRows as $rows2) {
            foreach ($rows2 as $row2) {
                $data[] = $row2;
            }
        }
        return $data;
    }

    protected function sortSpecificsListData(array $data, Account $courierAccount)
    {
        return $this->courierService->sortOrderListData(
            $data, $this->specificsListRequiredOrderFields, $this->specificsListRequiredParcelFields, true, $courierAccount
       );
    }

    /**
     * @return array
     */
    public function getSpecificsMetaDataFromRecords(array $records)
    {
        $orderMetaData = [];
        foreach ($records as $record) {
            $orderId = $record['orderId'];
            if (!isset($orderMetaData[$orderId])) {
                $orderMetaData[$orderId] = [];
            }
            if (isset($record['itemRow']) && $record['itemRow'] == true) {
                if (!isset($orderMetaData[$orderId]['itemRowCount'])) {
                    $orderMetaData[$orderId]['itemRowCount'] = 0;
                }
                $orderMetaData[$orderId]['itemRowCount']++;
            }
            if (isset($record['parcelRow']) && $record['parcelRow'] == true) {
                if (!isset($orderMetaData[$orderId]['parcelRowCount'])) {
                    $orderMetaData[$orderId]['parcelRowCount'] = 0;
                }
                $orderMetaData[$orderId]['parcelRowCount']++;
            }
        }
        return $orderMetaData;
    }

    /**
     * Get any options (e.g. package type, add-ons) for the given courier service
     * @return array
     */
    public function getCarrierOptionsForService($orderId, $accountId, $service)
    {
        $account = $this->accountService->fetch($accountId);
        $carrierOptions = $this->courierService->getCarrierOptions($account);
        $serviceOptions = $this->courierService->getCarrierOptions($account, $service);
        return $this->getFieldsRequirementStatus($serviceOptions, $carrierOptions);
    }

    /**
     * Get the potential values for the given courier service option (e.g. package type)
     * @return array
     */
    public function getDataForCarrierOption($option, $orderId, $accountId, $service = null)
    {
        $order = $this->orderService->fetch($orderId);
        $account = $this->accountService->fetch($accountId);
        $rootOu = $this->userOuService->getRootOuByActiveUser();
        $orders = new OrderCollection(Order::class, 'fetch', ['id' => $orderId]);
        $orders->attach($order);
        $productDetails = $this->getProductDetailsForOrders($orders, $rootOu);
        return $this->courierService->getCarrierOptionsProvider($account)
            ->getDataForCarrierBookingOption($option, $order, $account, $service, $rootOu, $productDetails);
    }

    protected function getCarrierServiceProvider(Account $account)
    {
        return $this->carrierServiceProviderRepository->getProviderForAccount($account);
    }

    // Required by GetProductDetailsForOrdersTrait
    protected function getProductDetailService()
    {
        return $this->productDetailService;
    }
}