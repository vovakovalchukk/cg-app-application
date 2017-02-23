<?php
namespace Orders\Courier;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Collection as AccountCollection;
use CG\Account\Shared\Entity as Account;
use CG\Channel\Shipping\Provider\BookingOptions\Repository as CarrierBookingOptionsRepository;
use CG\Channel\Shipping\Provider\Channels\Repository as ShippingChannelsProviderRepository;
use CG\Channel\Shipping\Provider\Service\CancelInterface as CarrierServiceProviderCancelInterface;
use CG\Channel\Shipping\Provider\Service\Repository as CarrierServiceProviderRepository;
use CG\Channel\Shipping\Services\Factory as ShippingServiceFactory;
use CG\Channel\Shipping\ServicesInterface;
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
use CG\Product\Detail\Entity as ProductDetail;
use CG\Product\Detail\Service as ProductDetailService;
use CG\Product\Entity as Product;
use CG\Product\Filter as ProductFilter;
use CG\Stdlib\DateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\OrganisationUnit\Service as UserOUService;
use DateTimeZone;
use Zend\Di\Di;

abstract class ServiceAbstract implements LoggerAwareInterface
{
    use LogTrait;

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
    protected function getCourierOptionsForOrder(Order $order, $selectedAccountId = null)
    {
        $shippingAccounts = $this->getShippingAccounts($order);
        return $this->shippingAccountsService->convertShippingAccountsToOptions($shippingAccounts, $selectedAccountId);
    }

    /**
     * @return AccountCollection
     */
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
     * @return OrderCollection
     */
    protected function fetchOrdersById(array $orderIds)
    {
        $filter = new OrderFilter();
        $filter->setLimit('all')
            ->setPage(1)
            ->setOrderIds($orderIds);
        return $this->orderService->fetchCollectionByFilter($filter);
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
}
