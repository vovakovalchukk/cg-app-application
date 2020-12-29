<?php
namespace Orders\Courier;

use CG\Account\Shared\Collection as AccountCollection;
use CG\Account\Shared\Entity as Account;
use CG\Account\Shipping\Service as AccountService;
use CG\Billing\Shipping\Ledger\Entity as ShippingLedger;
use CG\Billing\Shipping\Ledger\Service as ShippingLedgerService;
use CG\Channel\Shipping\Provider\Service\CancelInterface as CarrierServiceProviderCancelInterface;
use CG\Channel\Shipping\Provider\Service\CreateRestrictedInterface;
use CG\Channel\Shipping\Provider\Service\ExportInterface as CarrierServiceProviderExportInterface;
use CG\Channel\Shipping\Provider\Service\FetchRatesInterface as CarrierServiceProviderFetchRatesInterface;
use CG\Channel\Shipping\Provider\Service\Repository as CarrierServiceProviderRepository;
use CG\Channel\Shipping\Services\Factory as ShippingServiceFactory;
use CG\Locale\Length as LocaleLength;
use CG\Locale\Mass as LocaleMass;
use CG\Order\Client\Service as OrderService;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Courier\Label\OrderData;
use CG\Order\Shared\Courier\Label\OrderData\Collection as OrderDataCollection;
use CG\Order\Shared\Courier\Label\OrderParcelsData;
use CG\Order\Shared\Courier\Label\OrderParcelsData\Collection as OrderParcelsDataCollection;
use CG\Order\Shared\Courier\Label\OrderParcelsData\ParcelData;
use CG\Order\Shared\Item\Collection as ItemCollection;
use CG\Order\Shared\Item\Entity as Item;
use CG\Order\Shared\Label\Collection as OrderLabelCollection;
use CG\Order\Shared\Label\Entity as OrderLabel;
use CG\Order\Shared\Label\Filter as OrderLabelFilter;
use CG\Order\Shared\Label\Status as OrderLabelStatus;
use CG\Order\Shared\Label\StorageInterface as OrderLabelStorage;
use CG\Order\Shared\ShippableInterface as Order;
use CG\Order\Shared\Status as OrderStatus;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Product\Collection as ProductCollection;
use CG\Product\Detail\Collection as ProductDetailCollection;
use CG\Product\Detail\Entity as ProductDetail;
use CG\Product\Detail\Service as ProductDetailService;
use CG\Stdlib\DateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\User\OrganisationUnit\Service as UserOUService;
use DateTimeZone;
use Orders\Courier\SpecificsPage as SpecificsPageService;

class SpecificsAjax
{
    use GetProductDetailsForOrdersTrait;

    const DEFAULT_PARCELS = 1;
    const MIN_PARCELS = 1;
    const MAX_PARCELS = 9999;

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
    /** @var ShippingLedgerService */
    protected $shippingLedgerService;
    /** @var SpecificsPageService */
    protected $specificsPageService;

    protected $specificsListRequiredOrderFields = ['parcels', 'collectionDate', 'collectionTime'];
    protected $specificsListRequiredParcelFields = ['weight', 'width', 'height', 'length', 'packageType', 'itemParcelAssignment', 'deliveryExperience'];

    protected $parcelFieldsDefaults = [
        'width' => 0,
        'height' => 0,
        'length' => 0
    ];

    public function __construct(
        OrderService $orderService,
        AccountService $accountService,
        ShippingServiceFactory $shippingServiceFactory,
        UserOUService $userOuService,
        OrderLabelStorage $orderLabelStorage,
        CarrierServiceProviderRepository $carrierServiceProviderRepository,
        ProductDetailService $productDetailService,
        Service $courierService,
        ShippingLedgerService $shippingLedgerService,
        SpecificsPageService $specificsPageService
    ) {
        $this->orderService = $orderService;
        $this->accountService = $accountService;
        $this->shippingServiceFactory = $shippingServiceFactory;
        $this->userOuService = $userOuService;
        $this->orderLabelStorage = $orderLabelStorage;
        $this->carrierServiceProviderRepository = $carrierServiceProviderRepository;
        $this->productDetailService = $productDetailService;
        $this->courierService = $courierService;
        $this->shippingLedgerService = $shippingLedgerService;
        $this->specificsPageService = $specificsPageService;
    }

    public function getSpecificsListData(
        array $orderIds,
        int $courierAccountId,
        OrderDataCollection $ordersData = null,
        OrderParcelsDataCollection $ordersParcelsData = null
    ): array {
        $ordersData = $ordersData ?? new OrderDataCollection();
        $ordersParcelsData = $ordersParcelsData ?? new OrderParcelsDataCollection();

        $orders = $this->courierService->fetchOrdersById($orderIds);
        $this->courierService->removeZeroQuantityItemsFromOrders($orders);
        $courierAccount = $this->accountService->fetchShippingAccount((int) $courierAccountId);
        $data = $this->formatOrdersAsSpecificsListData($orders, $courierAccount, $ordersData, $ordersParcelsData);
        return $this->sortSpecificsListData($data, $courierAccount);
    }

    protected function formatOrdersAsSpecificsListData(
        OrderCollection $orders,
        Account $courierAccount,
        OrderDataCollection $ordersData,
        OrderParcelsDataCollection $ordersParcelsData
    ) {
        $specificsListData = [];
        $rootOu = $this->userOuService->getRootOuByActiveUser();
        $products = $this->courierService->getProductsForOrders($orders, $rootOu);
        $productDetails = $this->getProductDetailsForOrders($orders, $rootOu);
        $labels = $this->getOrderLabelsForOrders($orders);
        $carrierOptions = $this->courierService->getCarrierOptions($courierAccount);
        $eoriNumbers = $this->getEoriNumbersForAccount($courierAccount);
        foreach ($orders as $order) {
            $orderData = $this->courierService->getCommonOrderListData($order, $rootOu);
            $orderData['eoriNumbers'] = $eoriNumbers;
            unset($orderData['courier']);
            $orderLabel = null;
            $orderLabels = $labels->getBy('orderId', $order->getId());
            if (count($orderLabels) > 0) {
                $orderLabels->rewind();
                $orderLabel = $orderLabels->current();
            }
            /** @var OrderData $inputData */
            $inputData = ($ordersData->containsId($order->getId()) ? $ordersData->getById($order->getId()) : null);
            $options = $this->courierService->getCarrierOptions($courierAccount, $inputData ? $inputData->getService() : null);
            $specificsOrderData = $this->getSpecificsOrderListDataDefaults($order, $courierAccount, $options, $orderLabel);
            /** @var OrderParcelsData $parcelsInputData */
            $parcelsInputData = ($ordersParcelsData->containsId($order->getId()) ? $ordersParcelsData->getById($order->getId()) : null);

            $inputDataArray = ($inputData ? array_filter($inputData->toArray()) : []);
            if (isset($inputDataArray['service']) && $inputDataArray['service'] === "") {
                unset($inputDataArray['service']);
            }
            $orderData = array_merge($orderData, $specificsOrderData, $inputDataArray);
            $orderData = $this->checkOrderDataParcels($orderData, $order, $parcelsInputData);
            $itemsData = $this->formatOrderItemsAsSpecificsListData(
                $order->getItems(), $orderData, $products, $productDetails, $options, $carrierOptions, $rootOu
            );
            $parcelsData = $this->getParcelOrderListData(
                $order, $orderData, $options, $carrierOptions, $rootOu, $parcelsInputData
            );
            foreach ($parcelsData as $parcelData) {
                array_push($itemsData, $parcelData);
            }
            $specificsListData = array_merge($specificsListData, $itemsData);
        }
        $now = (new DateTime())->setTimezone(new DateTimeZone($this->getUsersTimezone()));
        foreach ($specificsListData as &$row) {
            $row['currentTime'] = $now->format('H:i');
            $row['timezone'] = $now->getTimezone()->getName();
        }
        $specificsListData = $this->performSumsOnSpecificsListData($specificsListData, $options);
        $specificsListData = $this->courierService->getCarrierOptionsProvider($courierAccount)
            ->addCarrierSpecificDataToListArray($specificsListData, $courierAccount, $rootOu, $orders, $productDetails);
        return $specificsListData;
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
        $exportable = ($providerService instanceof CarrierServiceProviderExportInterface
            && $providerService->isExportAllowedForOrder($courierAccount, $order));
        $cancellable = ($providerService instanceof CarrierServiceProviderCancelInterface
            && $providerService->isCancellationAllowedForOrder($courierAccount, $order));
        $dispatchable = ($order->getStatus() != OrderStatus::DISPATCHING)
            && OrderStatus::allowedStatusChange($order, OrderStatus::DISPATCHING);
        $rateable = ($providerService instanceof CarrierServiceProviderFetchRatesInterface
            && $providerService->isFetchRatesAllowedForOrder($courierAccount, $order));

        $creatable = true;
        if ($providerService instanceof CreateRestrictedInterface) {
            $creatable = $providerService->isCreateAllowedForOrder($courierAccount, $order, $orderLabel);
        }
        $data = [
            'parcels' => static::DEFAULT_PARCELS,
            // The order row will always be parcel 1, only parcel rows might be other numbers
            'parcelNumber' => 1,
            'labelStatus' => ($orderLabel ? $orderLabel->getStatus() : ''),
            'exportable' => $exportable,
            'cancellable' => $cancellable,
            'dispatchable' => $dispatchable,
            'rateable' => $rateable,
            'services' => $services,
            'creatable' => $creatable
        ];
        foreach ($options as $option) {
            $data[$option] = '';
            if ($option == 'collectionDate') {
                $data[$option] = date('Y-m-d');
            }
        }
        return $data;
    }

    protected function checkOrderDataParcels(array $orderData, Order $order, OrderParcelsData $parcelsData = null)
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
        if ($singleRow && $parcelsData && count($parcelsData->getParcels()) > 0) {
            $singleParcelData = $parcelsData->getParcels()->getFirst();
            $orderData = array_merge($orderData, $this->parcelDataToSpecificsListArray($singleParcelData));
        }
        return $orderData;
    }

    protected function parcelDataToSpecificsListArray(ParcelData $parcelData): array
    {
        $array = $parcelData->toArray();
        $array['itemParcelAssignment'] = json_encode($array['itemParcelAssignment']);
        return $array;
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
        array $carrierOptions,
        OrganisationUnit $rootOu
    ) {
        $itemsData = [];
        $itemCount = 0;
        $massUnit = LocaleMass::getForLocale($rootOu->getLocale());
        $lengthUnit = LocaleLength::getForLocale($rootOu->getLocale());
        foreach ($items as $item) {
            $rowData = null;
            if ($itemCount == 0) {
                $rowData = $orderData;
            }
            $itemData = $this->courierService->getCommonItemListData($item, $products, $rowData);
            $unitsData = $this->getUnitsOfMeasureData($rootOu);
            $specificsItemData = $this->getSpecificsItemListData($item, $productDetails, $options, $rowData);
            $specificsItemData['itemRow'] = true;
            $specificsItemData['showWeight'] = true;
            $specificsItemData['labelStatus'] = $orderData['labelStatus'];
            $specificsItemData['requiredFields'] = $this->getFieldsRequirementStatus($options, $carrierOptions);
            $itemsData[] = array_merge($itemData, $unitsData, $specificsItemData);
            $itemCount++;
        }
        return $itemsData;
    }

    protected function getUnitsOfMeasureData(OrganisationUnit $rootOu): array
    {
        return [
            'massUnit' => LocaleMass::getForLocale($rootOu->getLocale()),
            'lengthUnit' => LocaleLength::getForLocale($rootOu->getLocale()),
        ];
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
            return array_merge($data, [
                'width' => 0,
                'height' => 0,
                'length' => 0,
                'harmonisedSystemCode' => '',
                'countryOfOrigin' => ''
            ]);
        }

        $hsCode = $productDetail->getHsTariffNumber() && $productDetail->getHsTariffNumber() != '-' ? $productDetail->getHsTariffNumber() : '';
        $countryOfOrigin = $productDetail->getCountryOfManufacture() && $productDetail->getCountryOfManufacture() != '-' ? $productDetail->getCountryOfManufacture() : '';
        $locale = $this->userOuService->getActiveUserContainer()->getLocale();
        // Always add all product details even if there's no option for them as sometimes they're used indirectly
        $data['weight'] = $this->processWeightFromProductDetails($productDetail->getDisplayWeight($locale), $item);
        $data['width'] = $this->processDimensionFromProductDetails($productDetail->getDisplayWidth($locale), $item);
        $data['height'] = $this->processDimensionFromProductDetails($productDetail->getDisplayHeight($locale), $item);
        $data['length'] = $this->processDimensionFromProductDetails($productDetail->getDisplayLength($locale), $item);
        $data['harmonisedSystemCode'] = $hsCode;
        $data['countryOfOrigin'] = $countryOfOrigin;

        return $data;
    }

    protected function processWeightFromProductDetails($value, Item $item)
    {
        if ($value === null) {
            return 0;
        }
        return $value * $item->getItemQuantity();
    }

    protected function processDimensionFromProductDetails($value, Item $item)
    {
        // Impossible to tell how to multiply dimensions
        if ($value === null || $item->getItemQuantity() > 1) {
            return 0;
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
        array $options,
        array $carrierOptions,
        OrganisationUnit $rootOu,
        OrderParcelsData $parcelsInputData = null
    ) {
        $parcels = $orderData['parcels'];
        if (count($order->getItems()) <= 1 && $parcels <= 1) {
            return [];
        }

        $parcelsData = [];
        for ($parcel = 1; $parcel <= $parcels; $parcel++) {
            $childRowData = $this->courierService->getChildRowListData($order->getId(), $parcel);
            $unitsData = $this->getUnitsOfMeasureData($rootOu);
            $parcelData = array_merge($childRowData, $unitsData);
            $parcelData['parcelNumber'] = $parcel;
            $parcelData['parcelRow'] = true;
            $parcelData['showWeight'] = true;
            $parcelData['actionRow'] = ($parcel == $parcels);
            $parcelData['labelStatus'] = $orderData['labelStatus'];
            $parcelData['serviceOptions'] = $orderData['serviceOptions'];
            $parcelData['exportable'] = $orderData['exportable'];
            $parcelData['cancellable'] = $orderData['cancellable'];
            $parcelData['rateable'] = $orderData['rateable'];
            $parcelData['creatable'] = $orderData['creatable'];
            $parcelData['shippingCountryCode'] = $orderData['shippingCountryCode'];
            $parcelData['itemImageText'] = 'Package ' . $parcel;
            $parcelData['requiredFields'] = $this->getFieldsRequirementStatus($options, $carrierOptions);
            foreach ($options as $option) {
                $parcelData[$option] = $this->getParcelOptionData($option, $orderData);
            }

            if ($parcelsInputData && $parcelsInputData->getParcels()->containsId($parcel)) {
                $existingParcelData = $parcelsInputData->getParcels()->getById($parcel);
                $parcelData = array_merge($parcelData, $this->parcelDataToSpecificsListArray($existingParcelData));
            }

            $parcelsData[] = $parcelData;
        }
        return $parcelsData;
    }

    protected function getParcelOptionData(string $option, array $orderData)
    {
        if ($value = $orderData[$option] ?? null) {
            return $value;
        }

        return isset($this->parcelFieldsDefaults[$option]) ? $this->parcelFieldsDefaults[$option] : '';
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
        $account = $this->accountService->fetchShippingAccount((int) $accountId);
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
        $account = $this->accountService->fetchShippingAccount((int) $accountId);
        $rootOu = $this->userOuService->getRootOuByActiveUser();
        $orders = new OrderCollection(Order::class, 'fetch', ['id' => $orderId]);
        $orders->attach($order);
        $productDetails = $this->getProductDetailsForOrders($orders, $rootOu);
        return $this->courierService->getCarrierOptionsProvider($account)
            ->getDataForCarrierBookingOption($option, $order, $account, $service, $rootOu, $productDetails);
    }

    public function getShippingLedgerForActiveUser(): ShippingLedger
    {
        $rootOuId = $this->userOuService->getActiveUser()->getRootOuId();
        return $this->shippingLedgerService->fetch($rootOuId);
    }

    protected function getEoriNumbersForAccount(Account $account): array
    {
        $accounts = new AccountCollection(Account::class, __FUNCTION__);
        $accounts->attach($account);
        $organisationUnits = $this->specificsPageService->fetchOrganisationUnitsForAccounts($accounts);
        /** @var OrganisationUnit $ou */
        $ou = $organisationUnits->getById($account->getOrganisationUnitId());
        /** @var OrganisationUnit $rootOu */
        $rootOu = $ou->getRootEntity();

        $gb = $ou->getMetaData()->getEoriNumber() ?? $rootOu->getMetaData()->getEoriNumber();
        $ni = $ou->getMetaData()->getEoriNumberNi() ?? $rootOu->getMetaData()->getEoriNumberNi();
        $eu = $ou->getMetaData()->getEoriNumberEu() ?? $rootOu->getMetaData()->getEoriNumberEu();
        $eoriNumbers = array_filter([$gb, $ni, $eu]);

        return array_combine($eoriNumbers, $eoriNumbers);
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