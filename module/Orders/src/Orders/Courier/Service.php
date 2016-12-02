<?php
namespace Orders\Courier;

use CG\Account\Client\Filter as AccountFilter;
use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Collection as AccountCollection;
use CG\Account\Shared\Entity as Account;
use CG\Channel\Shipping\Provider\BookingOptions\ActionDescriptionsInterface;
use CG\Channel\Shipping\Provider\BookingOptions\Repository as CarrierBookingOptionsRepository;
use CG\Channel\Shipping\Provider\Channels\Repository as ShippingChannelsProviderRepository;
use CG\Channel\Shipping\Provider\Service\CancelInterface as CarrierServiceProviderCancelInterface;
use CG\Channel\Shipping\Provider\Service\Repository as CarrierServiceProviderRepository;
use CG\Channel\Shipping\Services\Factory as ShippingServiceFactory;
use CG\Order\Client\Service as OrderService;
use CG\Order\Service\Filter as OrderFilter;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Item\Collection as ItemCollection;
use CG\Order\Shared\Item\Entity as Item;
use CG\Order\Shared\Label\Collection as OrderLabelCollection;
use CG\Order\Shared\Label\Entity as OrderLabel;
use CG\Order\Shared\Label\Filter as OrderLabelFilter;
use CG\Order\Shared\Label\Status as OrderLabelStatus;
use CG\Order\Shared\Label\StorageInterface as OrderLabelStorage;
use CG\Order\Shared\Shipping\Conversion\Service as ShippingConversionService;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Product\Client\Service as ProductService;
use CG\Product\Collection as ProductCollection;
use CG\Product\Detail\Collection as ProductDetailCollection;
use CG\Product\Detail\Service as ProductDetailService;
use CG\Product\Detail\Entity as ProductDetail;
use CG\Product\Entity as Product;
use CG\Product\Filter as ProductFilter;
use CG\Stdlib\DateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\OrganisationUnit\Service as UserOUService;
use CG_UI\View\DataTable;
use DateTimeZone;
use Orders\Controller\CourierController;
use Orders\Courier\GetProductDetailsForOrdersTrait;
use Orders\Courier\ShippingAccountsService;
use Zend\Di\Di;
use Zend\Di\Exception\ClassNotFoundException;
use Zend\Stdlib\RequestInterface;

class Service implements LoggerAwareInterface
{
    use LogTrait;
    use GetProductDetailsForOrdersTrait;

    const OPTION_COLUMN_ALIAS = 'CourierSpecifics%sColumn';
    const DEFAULT_PARCELS = 1;
    const MIN_PARCELS = 1;
    const MAX_PARCELS = 10;
    const LOG_CODE = 'OrderCourierService';
    const LOG_OPTION_COLUMN_NOT_FOUND = 'No column alias called %s found for Account %d, channel %s';

    /** @var OrderService */
    protected $orderService;
    /** @var UserOUService */
    protected $userOuService;
    /** @var ShippingConversionService */
    protected $shippingConversionService;
    /** @var ProductService */
    protected $productService;
    /** @var AccountService */
    protected $accountService;
    /** @var ShippingServiceFactory */
    protected $shippingServiceFactory;
    /** @var OrderLabelStorage */
    protected $orderLabelStorage;
    /** @var ProductDetailService */
    protected $productDetailService;
    /** @var Di */
    protected $di;
    /** @var ShippingChannelsProviderRepository */
    protected $shippingChannelsProviderRepo;
    /** @var CarrierBookingOptionsRepository */
    protected $carrierBookingOptionsRepo;
    /** @var CarrierServiceProviderRepository */
    protected $carrierServiceProviderRepository;
    /** @var ShippingAccountsService */
    protected $shippingAccountsService;

    protected $reviewListRequiredFields = ['courier', 'service'];
    protected $specificsListRequiredOrderFields = ['parcels', 'collectionDate', 'collectionTime'];
    protected $specificsListRequiredParcelFields = ['weight', 'width', 'height', 'length', 'packageType', 'itemParcelAssignment'];

    public function __construct(
        OrderService $orderService,
        UserOUService $userOuService,
        ShippingConversionService $shippingConversionService,
        ProductService $productService,
        AccountService $accountService,
        ShippingServiceFactory $shippingServiceFactory,
        OrderLabelStorage $orderLabelStorage,
        ProductDetailService $productDetailService,
        Di $di,
        ShippingChannelsProviderRepository $shippingChannelsProviderRepo,
        CarrierBookingOptionsRepository $carrierBookingOptionsRepo,
        CarrierServiceProviderRepository $carrierServiceProviderRepository,
        ShippingAccountsService $shippingAccountsService
    ) {
        $this->setOrderService($orderService)
            ->setUserOuService($userOuService)
            ->setShippingConversionService($shippingConversionService)
            ->setProductService($productService)
            ->setAccountService($accountService)
            ->setShippingServiceFactory($shippingServiceFactory)
            ->setOrderLabelStorage($orderLabelStorage)
            ->setProductDetailService($productDetailService)
            ->setDi($di)
            ->setShippingChannelsProviderRepo($shippingChannelsProviderRepo)
            ->setCarrierBookingOptionsRepo($carrierBookingOptionsRepo)
            ->setCarrierServiceProviderRepository($carrierServiceProviderRepository)
            ->setShippingAccountsService($shippingAccountsService);
    }
    
    /**
     * @return array
     */
    public function getCourierOptionsForOrder(Order $order, $selectedAccountId = null)
    {
        $shippingAccounts = $this->getShippingAccounts($order);
        return $this->shippingAccountsService->convertShippingAccountsToOptions($shippingAccounts, $selectedAccountId);
    }

    /**
     * @param RequestInterface $request
     * @param OrderCollection $orders
     *
     * @return array $requestParams
     */
    public function getShippingAccountsForOrders(OrderCollection $orders)
    {
        $shippingAccounts = [];
        foreach ($orders as $order) {
            $shippingAccountsForOrder = $this->getShippingAccounts($order);
            $shippingAccounts = array_merge($shippingAccounts, $shippingAccountsForOrder->toArray());
        }

        return array_unique($shippingAccounts, SORT_REGULAR);
    }

    /**
     * @param $courierAccountId
     * @return \CG\Channel\Shipping\ServicesInterface
     */
    public function getShippingServiceForCourier($courierAccountId)
    {
        $courierAccount = $this->accountService->fetch($courierAccountId);
        return $this->shippingServiceFactory->createShippingService($courierAccount);
    }

    public function getShippingAccounts(Order $order = null)
    {
        $accounts = $this->shippingAccountsService->getProvidedShippingAccounts();
        $carrierAccounts = new AccountCollection(Account::class, __FUNCTION__);

        /** @var Account $account */
        foreach ($accounts as $account)
        {
            // Only show accounts that support the requested order
            $provider = $this->getShippingChannelsProvider($account);
            if ($order && !$provider->isOrderSupported($account->getChannel(), $order)) {
                continue;
            }

            $carrierAccounts->attach($account);
        }

        return $carrierAccounts;
    }

    /**
     * @return array
     */
    public function getCourierServiceOptions()
    {
        $shippingServicesByAccount = [];
        $shippingAccounts = $this->getShippingAccounts();
        foreach ($shippingAccounts as $account) {
            $shippingServicesByAccount[$account->getId()] = [];
            $shippingService = $this->shippingServiceFactory->createShippingService($account);
            $shippingServices = $shippingService->getShippingServices();
            $shippingServicesByAccount[$account->getId()] = $this->shippingServicesToOptions($shippingServices);
        }
        return $shippingServicesByAccount;
    }

    /**
     * @return array
     */
    public function getServicesOptionsForOrderAndAccount($orderId, $shippingAccountId)
    {
        $order = $this->orderService->fetch($orderId);
        $shippingAccount = $this->accountService->fetch($shippingAccountId);

        $shippingService = $this->shippingServiceFactory->createShippingService($shippingAccount);
        $shippingServices = $shippingService->getShippingServicesForOrder($order);
        return $this->shippingServicesToOptions($shippingServices);
    }

    protected function shippingServicesToOptions(array $shippingServices)
    {
        $options = [];
        foreach ($shippingServices as $value => $name) {
            $options[] = [
                'value' => $value,
                'title' => $name,
            ];
        }
        return $options;
    }

    /**
     * @return array
     */
    public function getReviewListData(array $orderIds)
    {
        $filter = new OrderFilter();
        $filter->setLimit('all')
            ->setPage(1)
            ->setOrderIds($orderIds);
        $orders = $this->orderService->fetchCollectionByFilter($filter);
        $this->removeZeroQuantityItemsFromOrders($orders);
        $data = $this->formatOrdersAsReviewListData($orders);
        return $this->sortReviewListData($data);
    }

    protected function formatOrdersAsReviewListData(OrderCollection $orders)
    {
        $data = [];
        $rootOu = $this->userOuService->getRootOuByActiveUser();
        $products = $this->getProductsForOrders($orders, $rootOu);
        foreach ($orders as $order) {
            $orderData = $this->getCommonOrderListData($order, $rootOu);
            $itemData = $this->formatOrderItemsAsReviewListData($order->getItems(), $orderData, $products);
            $data = array_merge($data, $itemData);
        }
        return $data;
    }

    protected function sortReviewListData(array $data)
    {
        return $this->sortOrderListData($data, $this->reviewListRequiredFields);
    }

    protected function getCommonOrderListData($order, $rootOu)
    {
        $shippingAlias = $this->shippingConversionService->fromMethodToAlias($order->getShippingMethod(), $rootOu);
        $shippingDescription = $order->getShippingMethod();
        $courierId = null;
        $services = null;
        $service = null;
        $serviceOptions = null;
        if ($shippingAlias) {
            $shippingDescription = $shippingAlias->getName();
            $courierId = $shippingAlias->getAccountId();
            if ($courierId) {
                $service = $shippingAlias->getShippingService();
                $serviceOptions = $shippingAlias->getOptions();
                $courierAccount = $this->accountService->fetch($courierId);
                $services = $this->shippingServiceFactory->createShippingService($courierAccount)->getShippingServicesForOrder($order);
                if (!isset($services[$service])) {
                    $service = null;
                }
            }
        }
        $shippingCountry = $order->getShippingAddressCountryForCourier();
        // 'United Kingdom' takes up a lot of space in the UI. As it is very common we'll drop it and only mention non-UK countries
        if ($shippingCountry == 'United Kingdom') {
            $shippingCountry = '';
        }

        $options = $this->getCourierOptionsForOrder($order, $courierId);
        if (count($options) == 1) {
            $index = key($options);
            $options[$index]['selected'] = true;
            $courierId = $options[$index]['value'];
            if (!$services) {
                $courierAccount = $this->accountService->fetch($courierId);
                $services = $this->shippingServiceFactory->createShippingService($courierAccount)->getShippingServicesForOrder($order);
            }
        }

        $orderData = [
            'orderRow' => true,
            'orderId' => $order->getId(),
            'buyerName' => $order->getBillingAddress()->getAddressFullName(),
            'shippingCountry' => $shippingCountry,
            'shippingCountryCode' => $order->getShippingAddressCountryCodeForCourier(),
            'postcode' => $order->getShippingAddressPostcodeForCourier(),
            'orderNumber' => $order->getExternalId(),
            'shippingMethod' => $shippingDescription,
            'courier' => (string) $courierId,
            'courierOptions' => [
                'name' => 'courier_' . $order->getId(),
                'class' => 'courier-courier-custom-select',
                'disabled' => (count($options) == 1),
                'blankOption' => false,
                'searchField' => false,
                'options' => $options,
            ],
            'services' => $services,
            'service' => $service,
            'serviceOptions' => $serviceOptions,
        ];
        return $orderData;
    }

    protected function formatOrderItemsAsReviewListData(
        ItemCollection $items,
        array $orderData,
        ProductCollection $products
    ) {
        $itemData = [];
        $itemCount = 0;
        foreach ($items as $item) {
            $rowData = null;
            if ($itemCount == 0) {
                $rowData = $orderData;
            }
            $itemData[] = $this->getCommonItemListData($item, $products, $rowData);
            $itemCount++;
        }
        return $itemData;
    }

    protected function getCommonItemListData(Item $item, ProductCollection $products, array $rowData = null)
    {
        if (!$rowData) {
            $rowData = $this->getChildRowListData($item->getOrderId());
        }
        $itemSpecifics = [
            'itemId' => $item->getId(),
            'itemImage' => $this->getImageUrlForOrderItem($item, $products),
            'itemName' => $this->getSanitisedItemName($item),
            'itemSku' => $item->getItemSku(),
            'quantity' => $item->getItemQuantity(),
        ];
        return array_merge($rowData, $itemSpecifics);
    }

    protected function getSanitisedItemName(Item $item)
    {
        // We sometimes append item options onto the name and separate them with newlines, strip those out
        return explode(PHP_EOL, $item->getItemName())[0];
    }

    protected function getChildRowListData($orderId)
    {
        return [
            'orderRow' => false,
            'orderId' => $orderId,
            'buyerName' => '',
            'buyerCountry' => '',
            'orderNumber' => '',
            'shippingMethod' => '',
            'courier' => '',
            'service' => '',
        ];
    }

    protected function getProductsForOrders(OrderCollection $orders, OrganisationUnit $rootOu)
    {
        $productSkus = [];
        $ouIds = [$rootOu->getId() => true];
        foreach ($orders as $order) {
            $ouIds[$order->getOrganisationUnitId()] = true;
            foreach ($order->getItems() as $item) {
                $productSkus[] = $item->getItemSku();
            }
        }

        try {
            $filter = (new ProductFilter())
                ->setLimit('all')
                ->setPage(1)
                ->setOrganisationUnitId(array_keys($ouIds))
                ->setSku($productSkus);
            return $this->productService->fetchCollectionByFilter($filter);
        } catch (NotFound $e) {
            return new ProductCollection(Product::class, 'empty');
        }
    }

    protected function getImageUrlForOrderItem(Item $item, ProductCollection $products)
    {
        // Note: we now store imageIds directly on Items. If you touch this code update it to use those. See CGIV-7005 for details.
        $imageUrl = '';
        $matchingProducts = $products->getBy('sku', $item->getItemSku());
        if (count($matchingProducts) > 0) {
            $matchingProducts->rewind();
            $product = $matchingProducts->current();
            foreach ($product->getImages() as $image) {
                $imageUrl = $image->getUrl();
                break;
            }
        }
        return $imageUrl;
    }

    public function fetchAccountsById($accountIds)
    {
        $filter = (new AccountFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setId($accountIds);
        return $this->accountService->fetchByFilter($filter);
    }

    public function alterSpecificsTableForSelectedCourier(DataTable $specificsTable, Account $selectedCourier)
    {
        $options = $this->getCarrierOptions($selectedCourier);
        // We always need the actions column but it must go last
        array_push($options, 'actions');
        foreach ($options as $option) {
            $columnAlias = sprintf(static::OPTION_COLUMN_ALIAS, ucfirst($option));
            try {
                $column = $this->di->get($columnAlias);
                $specificsTable->addColumn($column);
            } catch (ClassNotFoundException $e) {
                $this->logNotice(static::LOG_OPTION_COLUMN_NOT_FOUND, [$columnAlias, $selectedCourier->getId(), $selectedCourier->getChannel()], static::LOG_CODE);
                // No-op, allow for options with no matching column
            }
        }
    }

    protected function getShippingChannelsProvider(Account $account)
    {
        return $this->shippingChannelsProviderRepo->getProviderForAccount($account);
    }

    protected function getCarrierOptionsProvider(Account $account)
    {
        return $this->carrierBookingOptionsRepo->getProviderForAccount($account);
    }

    protected function getCarrierOptions(Account $account, $serviceCode = null)
    {
        return $this->getCarrierOptionsProvider($account)->getCarrierBookingOptionsForAccount($account, $serviceCode);
    }

    protected function getCarrierServiceProvider(Account $account)
    {
        return $this->carrierServiceProviderRepository->getProviderForAccount($account);
    }

    /**
     * @return string
     */
    public function getCreateActionDescription(Account $account)
    {
        return $this->getActionDescription('Create', 'Create label', $account);
    }

    /**
     * @return string
     */
    public function getCancelActionDescription(Account $account)
    {
        return $this->getActionDescription('Cancel', 'Cancel', $account);
    }

    /**
     * @return string
     */
    public function getPrintActionDescription(Account $account)
    {
        return $this->getActionDescription('Print', 'Print label', $account);
    }

    /**
     * @return string
     */
    public function getCreateAllActionDescription(Account $account)
    {
        return $this->getActionDescription('CreateAll', 'Create all labels', $account);
    }

    /**
     * @return string
     */
    public function getCancelAllActionDescription(Account $account)
    {
        return $this->getActionDescription('CancelAll', 'Cancel all', $account);
    }

    /**
     * @return string
     */
    public function getPrintAllActionDescription(Account $account)
    {
        return $this->getActionDescription('PrintAll', 'Print all labels', $account);
    }

    protected function getActionDescription($action, $defaultDescription, Account $account)
    {
        $provider = $this->getCarrierOptionsProvider($account);
        if (!$provider instanceof ActionDescriptionsInterface) {
            return $defaultDescription;
        }
        $method = 'get' . $action . 'ActionDescription';
        return $provider->$method();
    }

    /**
     * @return array
     */
    public function getSpecificsListData(array $orderIds, $courierAccountId, array $ordersData, array $ordersParcelsData)
    {
        $filter = new OrderFilter();
        $filter->setLimit('all')
            ->setPage(1)
            ->setOrderIds($orderIds);
        $orders = $this->orderService->fetchCollectionByFilter($filter);
        $this->removeZeroQuantityItemsFromOrders($orders);
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
        $products = $this->getProductsForOrders($orders, $rootOu);
        $productDetails = $this->getProductDetailsForOrders($orders, $rootOu);
        $labels = $this->getOrderLabelsForOrders($orders);
        $carrierOptions = $this->getCarrierOptions($courierAccount);
        foreach ($orders as $order) {
            $orderData = $this->getCommonOrderListData($order, $rootOu);
            unset($orderData['courier']);
            $orderLabel = null;
            $orderLabels = $labels->getBy('orderId', $order->getId());
            if (count($orderLabels) > 0) {
                $orderLabels->rewind();
                $orderLabel = $orderLabels->current();
            }
            $inputData = (isset($ordersData[$order->getId()]) ? $ordersData[$order->getId()] : []);
            $options = $this->getCarrierOptions($courierAccount, (isset($inputData['service']) ? $inputData['service'] : null));
            $specificsOrderData = $this->getSpecificsOrderListDataDefaults($order, $courierAccount, $options, $orderLabel);
            $parcelsInputData = (isset($ordersParcelsData[$order->getId()]) ? $ordersParcelsData[$order->getId()] : []);
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
        $data = $this->getCarrierOptionsProvider($courierAccount)->addCarrierSpecificDataToListArray($data, $courierAccount);
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
            $itemData = $this->getCommonItemListData($item, $products, $rowData);
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
            $parcelData = $this->getChildRowListData($order->getId(), $parcel);
            $parcelData['parcelNumber'] = $parcel;
            $parcelData['parcelRow'] = true;
            $parcelData['showWeight'] = true;
            $parcelData['actionRow'] = ($parcel == $parcels);
            $parcelData['labelStatus'] = $orderData['labelStatus'];
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
        $orderRows = $this->groupListDataByOrder($data);
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
        return $this->sortOrderListData(
            $data, $this->specificsListRequiredOrderFields, $this->specificsListRequiredParcelFields, true, $courierAccount
       );
    }

    protected function sortOrderListData(
        array $data,
        array $orderRequiredFields,
        array $parcelRequiredFields = [],
        $intersectServiceOptions = false,
        Account $courierAccount = null
    ) {
        // Separate out the fully pre-filled rows from those still requiring input
        $preFilledRows = [];
        $inputRequiredRows = [];
        $orderRows = $this->groupListDataByOrder($data);
        foreach ($orderRows as $orderId => $rows) {
            $complete = true;
            foreach ($rows as $row) {
                $rowOrderRequiredFields = $orderRequiredFields;
                $rowParcelRequiredFields = $parcelRequiredFields;
                if ($intersectServiceOptions) {
                    $options = $this->getCarrierOptions($courierAccount, (isset($row['service']) ? $row['service'] : null));
                    $rowOrderRequiredFields = array_intersect($orderRequiredFields, $options);
                    // service field is always required and can ocassionally get unset if the chosen service is not available
                    $rowOrderRequiredFields[] = 'service';
                    $rowParcelRequiredFields = array_intersect($parcelRequiredFields, $options);
                }
                if (isset($row['orderRow']) && $row['orderRow']) {
                    foreach ($rowOrderRequiredFields as $field) {
                        if (!$this->isOrderListRowFieldSet($row, $field)) {
                            $complete = false;
                            break 2;
                        }
                    }
                }
                if (isset($row['parcelRow']) && $row['parcelRow']) {
                    foreach ($rowParcelRequiredFields as $field) {
                        if (!$this->isOrderListRowFieldSet($row, $field)) {
                            $complete = false;
                            break 2;
                        }
                    }
                }
            }

            $group = ($complete ? 'Ready' : 'Input Required');
            ($complete ? $orderArray = &$preFilledRows : $orderArray = &$inputRequiredRows);

            $rows[0]['group'] = $group;
            foreach ($rows as $row) {
                $orderArray[] = $row;
            }
        }

        // Put rows requiring input at the top to make it easier for the user to find them
        return array_merge($inputRequiredRows, $preFilledRows);
    }

    protected function isOrderListRowFieldSet(array $row, $field)
    {
        if (!isset($row[$field]) || $row[$field] == '' || (is_numeric($row[$field]) && (float)$row[$field] == 0)) {
            return false;
        }
        return true;
    }

    protected function groupListDataByOrder(array $data)
    {
        $orderRows = [];
        foreach ($data as $row) {
            if (!isset($orderRows[$row['orderId']])) {
                $orderRows[$row['orderId']] = [];
            }
            $orderRows[$row['orderId']][] = $row;
        }
        return $orderRows;
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

    public function getCarrierOptionsForService($orderId, $accountId, $service)
    {
        $account = $this->accountService->fetch($accountId);
        $carrierOptions = $this->getCarrierOptions($account);
        $serviceOptions = $this->getCarrierOptions($account, $service);
        return $this->getFieldsRequirementStatus($serviceOptions, $carrierOptions);
    }

    public function getDataForCarrierOption($option, $orderId, $accountId, $service = null)
    {
        $order = $this->orderService->fetch($orderId);
        $account = $this->accountService->fetch($accountId);
        $rootOu = $this->userOuService->getRootOuByActiveUser();
        return $this->getCarrierOptionsProvider($account)->getDataForCarrierBookingOption($option, $order, $account, $service, $rootOu);
    }

    protected function setOrderService(OrderService $orderService)
    {
        $this->orderService = $orderService;
        return $this;
    }

    protected function setUserOuService(UserOUService $userOuService)
    {
        $this->userOuService = $userOuService;
        return $this;
    }

    protected function setShippingConversionService(ShippingConversionService $shippingConversionService)
    {
        $this->shippingConversionService = $shippingConversionService;
        return $this;
    }

    protected function setProductService(ProductService $productService)
    {
        $this->productService = $productService;
        return $this;
    }

    protected function setAccountService(AccountService $accountService)
    {
        $this->accountService = $accountService;
        return $this;
    }

    protected function setShippingServiceFactory(ShippingServiceFactory $shippingServiceFactory)
    {
        $this->shippingServiceFactory = $shippingServiceFactory;
        return $this;
    }

    protected function setOrderLabelStorage(OrderLabelStorage $orderLabelStorage)
    {
        $this->orderLabelStorage = $orderLabelStorage;
        return $this;
    }

    protected function setProductDetailService(ProductDetailService $productDetailService)
    {
        $this->productDetailService = $productDetailService;
        return $this;
    }

    protected function setDi(Di $di)
    {
        $this->di = $di;
        return $this;
    }

    protected function setShippingChannelsProviderRepo(ShippingChannelsProviderRepository $shippingChannelsProviderRepo)
    {
        $this->shippingChannelsProviderRepo = $shippingChannelsProviderRepo;
        return $this;
    }

    protected function setCarrierBookingOptionsRepo(CarrierBookingOptionsRepository $carrierBookingOptionsRepo)
    {
        $this->carrierBookingOptionsRepo = $carrierBookingOptionsRepo;
        return $this;
    }

    protected function setCarrierServiceProviderRepository(CarrierServiceProviderRepository $carrierServiceProviderRepository)
    {
        $this->carrierServiceProviderRepository = $carrierServiceProviderRepository;
        return $this;
    }

    protected function setShippingAccountsService(ShippingAccountsService $shippingAccountsService)
    {
        $this->shippingAccountsService = $shippingAccountsService;
        return $this;
    }

    // Required by GetProductDetailsForOrdersTrait
    protected function getProductDetailService()
    {
        return $this->productDetailService;
    }
}
