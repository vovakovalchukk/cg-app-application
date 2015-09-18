<?php
namespace Orders\Courier;

use CG\Account\Client\Filter as AccountFilter;
use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Entity as Account;
use CG\Channel\ShippingServiceFactory;
use CG\Channel\Type as ChannelType;
use CG\Dataplug\Carrier\Service as CarrierService;
use CG\Order\Client\Service as OrderService;
use CG\Order\Shared\Label\Collection as OrderLabelCollection;
use CG\Order\Shared\Label\Entity as OrderLabel;
use CG\Order\Shared\Label\Filter as OrderLabelFilter;
use CG\Order\Shared\Label\Status as OrderLabelStatus;
use CG\Order\Shared\Label\StorageInterface as OrderLabelStorage;
use CG\Order\Service\Filter as OrderFilter;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Item\Collection as ItemCollection;
use CG\Order\Shared\Item\Entity as Item;
use CG\Order\Shared\Shipping\Conversion\Service as ShippingConversionService;
use CG\Product\Filter as ProductFilter;
use CG\Product\Client\Service as ProductService;
use CG\Product\Collection as ProductCollection;
use CG\Product\Entity as Product;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG_UI\View\DataTable;
use CG\User\OrganisationUnit\Service as UserOUService;
use Zend\Di\Di;
use Zend\Di\Exception\ClassNotFoundException;

class Service implements LoggerAwareInterface
{
    use LogTrait;

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
    /** @var CarrierService */
    protected $carrierService;
    /** @var OrderLabelStorage */
    protected $orderLabelStorage;
    /** @var Di */
    protected $di;

    protected $shippingAccounts;

    public function __construct(
        OrderService $orderService,
        UserOUService $userOuService,
        ShippingConversionService $shippingConversionService,
        ProductService $productService,
        AccountService $accountService,
        ShippingServiceFactory $shippingServiceFactory,
        CarrierService $carrierService,
        OrderLabelStorage $orderLabelStorage,
        Di $di
    ) {
        $this->setOrderService($orderService)
            ->setUserOuService($userOuService)
            ->setShippingConversionService($shippingConversionService)
            ->setProductService($productService)
            ->setAccountService($accountService)
            ->setShippingServiceFactory($shippingServiceFactory)
            ->setCarrierService($carrierService)
            ->setOrderLabelStorage($orderLabelStorage)
            ->setDi($di);
    }
    
    /**
     * @return array
     */
    public function getCourierOptions()
    {
        $shippingAccounts = $this->getShippingAccounts();
        $courierOptions = [];
        foreach ($shippingAccounts as $shippingAccount) {
            $courierOptions[] = [
                'value' => $shippingAccount->getId(),
                'title' => $shippingAccount->getDisplayName(),
            ];
        }
        return $courierOptions;
    }

    public function getCourierServiceOptions()
    {
        $shippingServicesByAccount = [];
        $shippingAccounts = $this->getShippingAccounts();
        foreach ($shippingAccounts as $account) {
            $shippingServicesByAccount[$account->getId()] = [];
            $shippingService = $this->shippingServiceFactory->createShippingService($account);
            $shippingServices = $shippingService->getShippingServices();
            foreach ($shippingServices as $value => $name) {
                $shippingServicesByAccount[$account->getId()][] = [
                    'value' => $value,
                    'title' => $name,
                ];
            }
        }
        return $shippingServicesByAccount;
    }

    protected function getShippingAccounts()
    {
        if ($this->shippingAccounts) {
            return $this->shippingAccounts;
        }
        $ouIds = $this->userOuService->getAncestorOrganisationUnitIdsByActiveUser();
        $filter = (new AccountFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setOrganisationUnitId($ouIds)
            ->setType(ChannelType::SHIPPING);
        $this->shippingAccounts =  $this->accountService->fetchByFilter($filter);
        return $this->shippingAccounts;
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
        return $this->formatOrdersAsReviewListData($orders);
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

    protected function getCommonOrderListData($order, $rootOu)
    {
        $shippingAlias = $this->shippingConversionService->fromMethodToAlias($order->getShippingMethod(), $rootOu);
        $shippingDescription = $order->getShippingMethod();
        $courierId = null;
        $service = null;
        if ($shippingAlias) {
            $shippingDescription = $shippingAlias->getName();
            $courierId = $shippingAlias->getAccountId();
            $service = $shippingAlias->getShippingService();
        }

        $orderData = [
            'orderRow' => true,
            'orderId' => $order->getId(),
            'buyerName' => $order->getBillingAddress()->getAddressFullName(),
            'shippingCountry' => $order->getShippingAddress()->getAddressCountry(),
            'orderNumber' => $order->getExternalId(),
            'shippingMethod' => $shippingDescription,
            'courier' => (string)$courierId,
            'service' => $service,
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
            'itemName' => $item->getItemName(),
            'itemSku' => $item->getItemSku(),
            'quantity' => $item->getItemQuantity(),
        ];
        return array_merge($rowData, $itemSpecifics);
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

        $filter = (new ProductFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setOrganisationUnitId(array_keys($ouIds))
            ->setSku($productSkus);
        try {
            return $this->productService->fetchCollectionByFilter($filter);
        } catch (NotFound $e) {
            return new ProductCollection(Product::class, 'empty');
        }
    }

    protected function getImageUrlForOrderItem(Item $item, ProductCollection $products)
    {
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

    protected function getCarrierOptions(Account $account)
    {
        $carrier = $this->carrierService->getCarrierForAccount($account);
        $options = array_merge($this->carrierService->getDefaultOptions(), $carrier->getOptions());
        return array_keys(array_filter($options));
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
        $courierAccount = $this->accountService->fetch($courierAccountId);
        return $this->formatOrdersAsSpecificsListData($orders, $courierAccount, $ordersData, $ordersParcelsData);
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
        $labels = $this->getOrderLabelsForOrders($orders);
        $options = $this->getCarrierOptions($courierAccount);
        foreach ($orders as $order) {
            $orderData = $this->getCommonOrderListData($order, $rootOu);
            unset($orderData['courier']);
            $orderLabel = null;
            $orderLabels = $labels->getBy('orderId', $order->getId());
            if (count($orderLabels) > 0) {
                $orderLabels->rewind();
                $orderLabel = $orderLabels->current();
            }
            $specificsOrderData = $this->getSpecificsOrderListDataDefaults($order, $options, $orderLabel);
            $inputData = (isset($ordersData[$order->getId()]) ? $ordersData[$order->getId()] : []);
            $parcelsInputData = (isset($ordersParcelsData[$order->getId()]) ? $ordersParcelsData[$order->getId()] : []);
            $orderData = array_merge($orderData, $specificsOrderData, $inputData);
            $orderData = $this->checkOrderDataParcels($orderData, $parcelsInputData, $order);
            $itemsData = $this->formatOrderItemsAsSpecificsListData($order->getItems(), $orderData, $products, $options);
            $parcelsData = $this->getParcelOrderListData($order, $options, $orderData, $parcelsInputData);
            foreach ($parcelsData as $parcelData) {
                array_push($itemsData, $parcelData);
            }
            $data = array_merge($data, $itemsData);
        }
        return $data;
    }

    protected function getSpecificsOrderListDataDefaults(Order $order, array $options, OrderLabel $orderLabel = null)
    {
        $data = [
            'collectionDate' => date('d/m/Y'),
            'parcels' => static::DEFAULT_PARCELS,
            // The order row will always be parcel 1, only parcel rows might be other numbers
            'parcelNumber' => 1,
            'labelStatus' => ($orderLabel ? $orderLabel->getStatus() : OrderLabelStatus::NOT_PRINTED),
        ];
        foreach ($options as $option) {
            $data[$option] = '';
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
        $orderData['showWeight'] = ($orderData['parcels'] == 1);

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
        $filter = (new OrderLabelFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setOrderId($orderIds);
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
        array $options
    ) {
        $itemsData = [];
        $itemCount = 0;
        foreach ($items as $item) {
            $rowData = null;
            if ($itemCount == 0) {
                $rowData = $orderData;
            }
            $itemData = $this->getCommonItemListData($item, $products, $rowData);
            $specificsItemData = $this->getSpecificsItemListData($item, $options, $rowData);
            $specificsItemData['showWeight'] = $orderData['showWeight'];
            $itemsData[] = array_merge($itemData, $specificsItemData);
            $itemCount++;
        }
        return $itemsData;
    }

    protected function getSpecificsItemListData(Item $item, array $options, array $rowData = null)
    {
        if ($rowData) {
            return [];
        }
        $data = [
            'parcels' => '',
        ];
        foreach ($options as $option) {
            $data[$option] = '';
        }
        return $data;
    }

    protected function getParcelOrderListData(Order $order, array $options, array $orderData, array $parcelsInputData)
    {
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
            foreach ($options as $option) {
                $parcelData[$option] = (isset($orderData[$option]) ? $orderData[$option] : '');
            }
            $optionKeys = array_flip($options);
            if (isset($optionKeys['weight']) || isset($optionKeys['width']) || isset($optionKeys['height']) || isset($optionKeys['length'])) {
                $itemImageText = 'Total package ';
                $itemImageTextAdtnl = [];
                if (isset($optionKeys['weight'])) {
                    $itemImageTextAdtnl[] = 'weight';
                }
                if (isset($optionKeys['width']) || isset($optionKeys['height']) || isset($optionKeys['length'])) {
                    $itemImageTextAdtnl[] = 'dimensions';
                }
                $itemImageText .= implode(' and ', $itemImageTextAdtnl);
                $parcelData['itemImageText'] = $itemImageText;
            }

            if (isset($parcelsInputData[$parcel])) {
                $parcelData = array_merge($parcelData, $parcelsInputData[$parcel]);
            }

            $parcelsData[] = $parcelData;
        }
        return $parcelsData;
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

    protected function setCarrierService(CarrierService $carrierService)
    {
        $this->carrierService = $carrierService;
        return $this;
    }

    protected function setOrderLabelStorage(OrderLabelStorage $orderLabelStorage)
    {
        $this->orderLabelStorage = $orderLabelStorage;
        return $this;
    }

    protected function setDi(Di $di)
    {
        $this->di = $di;
        return $this;
    }
}