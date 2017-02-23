<?php
namespace Orders\Courier;

use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Item\Collection as ItemCollection;
use CG\Product\Collection as ProductCollection;

class ReviewAjax extends ServiceAbstract
{
    protected $reviewListRequiredFields = ['courier', 'service'];

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
        $orders = $this->fetchOrdersById($orderIds);
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
}