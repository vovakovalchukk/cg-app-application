<?php
namespace Orders\Courier;

use CG\Account\Client\Filter as AccountFilter;
use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Entity as Account;
use CG\Channel\ShippingServiceFactory;
use CG\Channel\Type as ChannelType;
use CG\Dataplug\Carrier\Service as CarrierService;
use CG\Order\Client\Service as OrderService;
use CG\Order\Service\Filter as OrderFilter;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Item\Collection as ItemCollection;
use CG\Order\Shared\Item\Entity as Item;
use CG\Order\Shared\Shipping\Conversion\Service as ShippingConversionService;
use CG\Product\Filter as ProductFilter;
use CG\Product\Client\Service as ProductService;
use CG\Product\Collection as ProductCollection;
use CG\OrganisationUnit\Entity as OrganisationUnit;
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
        Di $di
    ) {
        $this->setOrderService($orderService)
            ->setUserOuService($userOuService)
            ->setShippingConversionService($shippingConversionService)
            ->setProductService($productService)
            ->setAccountService($accountService)
            ->setShippingServiceFactory($shippingServiceFactory)
            ->setCarrierService($carrierService)
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
                'orderId' => $order->getId(),
                'buyerName' => $order->getBillingAddress()->getAddressFullName(),
                'shippingCountry' => $order->getShippingAddress()->getAddressCountry(),
                'orderNumber' => $order->getExternalId(),
                'shippingMethod' => $shippingDescription,
                'courier' => (string)$courierId,
                'service' => $service,
            ];
            $itemData = $this->formatOrderItemsAsReviewListData($order->getItems(), $orderData, $products);
            $data = array_merge($data, $itemData);
        }
        return $data;
    }

    protected function formatOrderItemsAsReviewListData(
        ItemCollection $items,
        array $orderData,
        ProductCollection $products
    ) {
        $itemData = [];
        $itemCount = 0;
        foreach ($items as $item) {
            if ($itemCount == 0) {
                $rowData = $orderData;
            } else {
                $rowData = [
                    'orderId' => $orderData['orderId'],
                    'childRow' => true,
                    'buyerName' => '',
                    'buyerCountry' => '',
                    'orderNumber' => '',
                    'shippingMethod' => '',
                    'courier' => '',
                    'service' => '',
                ];
            }
            $itemSpecifics = [
                'itemId' => $item->getId(),
                'itemImage' => $this->getImageUrlForOrderItem($item, $products),
                'itemName' => $item->getItemName(),
                'itemSku' => $item->getItemSku(),
                'quantity' => $item->getItemQuantity(),
            ];
            $itemData[] = array_merge($rowData, $itemSpecifics);
            $itemCount++;
        }
        return $itemData;
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
        return $this->productService->fetchCollectionByFilter($filter);
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
        $carrier = $this->carrierService->getCarrierForAccount($selectedCourier);
        $options = array_merge($this->carrierService->getDefaultOptions(), $carrier->getOptions());
        $requiredOptions = array_keys(array_filter($options));
        // We always need the actions column but it must go last
        array_push($requiredOptions, 'actions');
        foreach ($requiredOptions as $option) {
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

    protected function setDi(Di $di)
    {
        $this->di = $di;
        return $this;
    }
}