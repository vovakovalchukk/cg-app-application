<?php
namespace Orders\Courier;

use CG\Account\Client\Service as AccountService;
use CG\Channel\Shipping\Services\Factory as ShippingServiceFactory;
use CG\Order\Client\Service as OrderService;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Item\Collection as ItemCollection;
use CG\Order\Shared\Shipping\Conversion\Service as ShippingConversionService;
use CG\Product\Collection as ProductCollection;
use CG\User\OrganisationUnit\Service as UserOUService;
use Orders\Courier\Service;

class ReviewAjax
{
    /** @var OrderService */
    protected $orderService;
    /** @var AccountService */
    protected $accountService;
    /** @var ShippingServiceFactory */
    protected $shippingServiceFactory;
    /** @var ShippingConversionService */
    protected $shippingConversionService;
    /** @var UserOUService */
    protected $userOuService;
    /** @var Service */
    protected $courierService;

    protected $reviewListRequiredFields = ['courier', 'service'];

    public function __construct(
        OrderService $orderService,
        AccountService $accountService,
        ShippingServiceFactory $shippingServiceFactory,
        ShippingConversionService $shippingConversionService,
        UserOUService $userOuService,
        Service $courierService
    ) {
        $this->orderService = $orderService;
        $this->accountService = $accountService;
        $this->shippingServiceFactory = $shippingServiceFactory;
        $this->shippingConversionService = $shippingConversionService;
        $this->userOuService = $userOuService;
        $this->courierService = $courierService;
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
     * @return array Shipment data for the selected orders formatted for DataTables
     */
    public function getReviewListData(array $orderIds)
    {
        $orders = $this->courierService->fetchOrdersById($orderIds);
        $this->courierService->removeZeroQuantityItemsFromOrders($orders);
        $this->courierService->preFetchShippingServicesForOrders($orders, $this->getShippingAliasCouriersForOrders($orders));
        $data = $this->formatOrdersAsReviewListData($orders);
        return $this->sortReviewListData($data);
    }

    protected function formatOrdersAsReviewListData(OrderCollection $orders)
    {
        $data = [];
        $rootOu = $this->userOuService->getRootOuByActiveUser();
        $products = $this->courierService->getProductsForOrders($orders, $rootOu);
        foreach ($orders as $order) {
            $orderData = $this->courierService->getCommonOrderListData($order, $rootOu);
            $itemData = $this->formatOrderItemsAsReviewListData($order->getItems(), $orderData, $products);
            $data = array_merge($data, $itemData);
        }
        return $data;
    }

    protected function getShippingAliasCouriersForOrders(OrderCollection $orders)
    {
        $rootOu = $this->userOuService->getRootOuByActiveUser();
        $orderCourierIds = [];
        foreach ($orders as $order) {
            $shippingAlias = $this->shippingConversionService->fromMethodToAlias($order->getShippingMethod(), $rootOu);
            if (!$shippingAlias || !$shippingAlias->getAccountId()) {
                continue;
            }
            $orderCourierIds[$order->getId()] = $shippingAlias->getAccountId();
        }
        return $orderCourierIds;
    }

    protected function sortReviewListData(array $data)
    {
        return $this->courierService->sortOrderListData($data, $this->reviewListRequiredFields);
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
            $itemData[] = $this->courierService->getCommonItemListData($item, $products, $rowData);
            $itemCount++;
        }
        return $itemData;
    }
}