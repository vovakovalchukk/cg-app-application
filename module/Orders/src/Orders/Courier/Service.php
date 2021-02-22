<?php
namespace Orders\Courier;

use CG\Account\Shared\Collection as AccountCollection;
use CG\Account\Shared\Entity as Account;
use CG\Account\Shipping\Service as AccountService;
use CG\Channel\Shipping\Provider\BookingOptions\Repository as CarrierBookingOptionsRepository;
use CG\Channel\Shipping\Provider\Channels\Repository as ShippingChannelsProviderRepository;
use CG\Channel\Shipping\ProviderInterface;
use CG\Channel\Shipping\Services\Factory as ShippingServiceFactory;
use CG\Order\Client\Service as OrderService;
use CG\Order\Service\Filter as OrderFilter;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Item\Collection as ItemCollection;
use CG\Order\Shared\Item\Entity as Item;
use CG\Order\Shared\ShippableInterface as Order;
use CG\Order\Shared\Shipping\Conversion\Service as ShippingConversionService;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Product\Client\Service as ProductService;
use CG\Product\Collection as ProductCollection;
use CG\Product\Entity as Product;
use CG\Product\Filter as ProductFilter;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Runtime\NotInUseException;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    /** @var OrderService */
    protected $orderService;
    /** @var ShippingConversionService */
    protected $shippingConversionService;
    /** @var ProductService */
    protected $productService;
    /** @var AccountService */
    protected $accountService;
    /** @var ShippingServiceFactory */
    protected $shippingServiceFactory;
    /** @var ShippingChannelsProviderRepository */
    protected $shippingChannelsProviderRepo;
    /** @var CarrierBookingOptionsRepository */
    protected $carrierBookingOptionsRepo;
    /** @var ShippingAccountsService */
    protected $shippingAccountsService;

    public function __construct(
        OrderService $orderService,
        ShippingConversionService $shippingConversionService,
        ProductService $productService,
        AccountService $accountService,
        ShippingServiceFactory $shippingServiceFactory,
        ShippingChannelsProviderRepository $shippingChannelsProviderRepo,
        CarrierBookingOptionsRepository $carrierBookingOptionsRepo,
        ShippingAccountsService $shippingAccountsService
    ) {
        $this->orderService = $orderService;
        $this->shippingConversionService = $shippingConversionService;
        $this->productService = $productService;
        $this->accountService = $accountService;
        $this->shippingServiceFactory = $shippingServiceFactory;
        $this->shippingChannelsProviderRepo = $shippingChannelsProviderRepo;
        $this->carrierBookingOptionsRepo = $carrierBookingOptionsRepo;
        $this->shippingAccountsService = $shippingAccountsService;
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
     * @return array shippingAccounts
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
            if ($order && !$provider->isOrderSupported($account, $order)) {
                continue;
            }

            $carrierAccounts->attach($account);
        }

        return $carrierAccounts;
    }

    /**
     * @return OrderCollection
     */
    public function fetchOrdersById(array $orderIds)
    {
        if (empty($orderIds)) {
            return new OrderCollection(Order::class, __FUNCTION__, ['orderIds' => $orderIds]);
        }
        $filter = new OrderFilter();
        $filter->setLimit('all')
            ->setPage(1)
            ->setOrderIds($orderIds);
        return $this->orderService->fetchLinkedCollectionByFilter($filter);
    }

    /**
     * @return array
     */
    public function getCommonOrderListData(Order $order, OrganisationUnit $rootOu)
    {
        $shippingAlias = $this->shippingConversionService->fromMethodToAlias($order->getShippingMethod(), $rootOu);
        $shippingDescription = $order->getShippingMethod();
        $courierId = null;
        $services = null;
        $service = null;
        $serviceOptions = null;

        try {
            if ($shippingAlias) {
                $shippingDescription = $shippingAlias->getName();
                $courierId = $shippingAlias->getAccountId();
                if ($courierId) {
                    $service = $shippingAlias->getShippingService();
                    $serviceOptions = $shippingAlias->getOptions();
                    $courierAccount = $this->accountService->fetchShippingAccount((int) $courierId);

                    if (!$this->shippingChannelsProviderRepo->isProvidedAccount($courierAccount)) {
                        throw new NotInUseException('Royal Mail PPI is not used in Courier UI');
                    }

                    $services = $this->shippingServiceFactory->createShippingService($courierAccount)->getShippingServicesForOrder($order);

                    if (!isset($services[$service])) {
                        $service = null;
                    }
                }
            }
        } catch (NotInUseException $e) {
            $courierId = null;
            $services = null;
            $service = null;
            $serviceOptions = null;
        }
        $shippingCountry = $order->getShippingAddressCountryForCourier();
        // 'United Kingdom' takes up a lot of space in the UI. As it is very common we'll drop it and only mention non-UK countries
        if ($shippingCountry == 'United Kingdom') {
            $shippingCountry = '';
        }

        $couriers = $this->getCourierOptionsForOrder($order, $courierId);
        // If there's only one courier pre-select it
        if (count($couriers) == 1) {
            $index = key($couriers);
            $couriers[$index]['selected'] = true;
            $courierId = $couriers[$index]['value'];
            if (!$services) {
                $courierAccount = $this->accountService->fetchShippingAccount((int) $courierId);
                $services = $this->shippingServiceFactory->createShippingService($courierAccount)->getShippingServicesForOrder($order);
            }
        }
        // If there's only one courier service pre-select it
        if (count($services) == 1) {
            reset($services);
            $service = key($services);
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
                'disabled' => (count($couriers) == 1),
                'blankOption' => false,
                'searchField' => false,
                'options' => $couriers,
            ],
            'services' => $services,
            'service' => $service,
            'serviceOptions' => $serviceOptions,
        ];
        return $orderData;
    }

    /**
     * @return array
     */
    public function getCommonItemListData(Item $item, ProductCollection $products, array $rowData = null)
    {
        if (!$rowData) {
            $rowData = $this->getChildRowListData($item->getLinkedOrderId());
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

    /**
     * @return array
     */
    public function getChildRowListData($orderId)
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

    /**
     * @return ProductCollection
     */
    public function getProductsForOrders(OrderCollection $orders, OrganisationUnit $rootOu)
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

    /**
     * @return ProviderInterface
     */
    public function getCarrierOptionsProvider(Account $account)
    {
        return $this->carrierBookingOptionsRepo->getProviderForAccount($account);
    }

    /**
     * @return array
     */
    public function getCarrierOptions(Account $account, $serviceCode = null)
    {
        $this->logDebug(get_class($this->getCarrierOptionsProvider($account)), [], 'MYTEST');

        $this->logDebugDump($this->getCarrierOptionsProvider($account)->getCarrierBookingOptionsForAccount($account, $serviceCode), 'OPTIONS', [], 'MYTEST');

        return $this->getCarrierOptionsProvider($account)->getCarrierBookingOptionsForAccount($account, $serviceCode);
    }

    /**
     * @return array
     */
    public function sortOrderListData(
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

    /**
     * @return array
     */
    public function groupListDataByOrder(array $data)
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

    public function removeZeroQuantityItemsFromOrders(OrderCollection $orders)
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
}
