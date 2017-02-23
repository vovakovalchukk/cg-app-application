<?php
namespace Orders\Courier;

use CG\Account\Client\Service as AccountService;
use CG\Channel\Shipping\Services\Factory as ShippingServiceFactory;
use CG\Order\Client\Service as OrderService;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Item\Collection as ItemCollection;
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
    /** @var UserOUService */
    protected $userOuService;
    /** @var Service */
    protected $service;

    protected $reviewListRequiredFields = ['courier', 'service'];

    public function __construct(
        OrderService $orderService,
        AccountService $accountService,
        ShippingServiceFactory $shippingServiceFactory,
        UserOUService $userOuService,
        Service $service
    ) {
        $this->orderService = $orderService;
        $this->accountService = $accountService;
        $this->shippingServiceFactory = $shippingServiceFactory;
        $this->userOuService = $userOuService;
        $this->service = $service;
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
        $orders = $this->service->fetchOrdersById($orderIds);
        $this->service->removeZeroQuantityItemsFromOrders($orders);
        $data = $this->formatOrdersAsReviewListData($orders);
        return $this->sortReviewListData($data);
    }

    protected function formatOrdersAsReviewListData(OrderCollection $orders)
    {
        $data = [];
        $rootOu = $this->userOuService->getRootOuByActiveUser();
        $products = $this->service->getProductsForOrders($orders, $rootOu);
        foreach ($orders as $order) {
            $orderData = $this->service->getCommonOrderListData($order, $rootOu);
            $itemData = $this->formatOrderItemsAsReviewListData($order->getItems(), $orderData, $products);
            $data = array_merge($data, $itemData);
        }
        return $data;
    }

    protected function sortReviewListData(array $data)
    {
        return $this->service->sortOrderListData($data, $this->reviewListRequiredFields);
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
            $itemData[] = $this->service->getCommonItemListData($item, $products, $rowData);
            $itemCount++;
        }
        return $itemData;
    }
}