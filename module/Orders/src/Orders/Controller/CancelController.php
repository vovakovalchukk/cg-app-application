<?php
namespace Orders\Controller;

use Zend\Di\Di;
use CG\Stdlib\Exception\Runtime\NotFound;
use Exception;
use Zend\Mvc\Controller\AbstractActionController;
use Orders\Order\Service as OrderService;
use Orders\Filter\Service as FilterService;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Mapper as OrderMapper;
use CG\Channel\Gearman\Generator\Order\Cancel as OrderCanceller;
use CG\Order\Shared\Cancel\Value as CancelValue;
use Zend\View\Model\JsonModel;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stdlib\DateTime;

class CancelController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;

    protected $di;
    protected $orderService;
    protected $filterService;
    protected $orderCanceller;
    protected $jsonModelFactory;

    public function __construct(
        Di $di,
        OrderService $orderService,
        FilterService $filterService,
        OrderCanceller $orderCanceller,
        JsonModelFactory $jsonModelFactory
    ) {
        $this
            ->setDi($di)
            ->setOrderService($orderService)
            ->setFilterService($filterService)
            ->setOrderCanceller($orderCanceller)
            ->setJsonModelFactory($jsonModelFactory);
    }

    public function setDi(Di $di)
    {
        $this->di = $di;
        return $this;
    }

    /**
     * @return Di
     */
    public function getDi()
    {
        return $this->di;
    }

    public function setOrderService(OrderService $orderService)
    {
        $this->orderService = $orderService;
        return $this;
    }

    /**
     * @return OrderService
     */
    public function getOrderService()
    {
        return $this->orderService;
    }

    public function setFilterService(FilterService $filterService)
    {
        $this->filterService = $filterService;
        return $this;
    }

    /**
     * @return FilterService
     */
    public function getFilterService()
    {
        return $this->filterService;
    }

    public function setOrderCanceller(OrderCanceller $orderCanceller)
    {
        $this->orderCanceller = $orderCanceller;
        return $this;
    }

    /**
     * @return OrderCanceller
     */
    public function getOrderCanceller()
    {
        return $this->orderCanceller;
    }

    public function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }

    /**
     * @return JsonModelFactory
     */
    public function getJsonModelFactory()
    {
        return $this->jsonModelFactory;
    }

    /**
     * @return JsonModel
     */
    protected function getDefaultJsonResponse()
    {
        return $this->getJsonModelFactory()->newInstance(
            [
                'cancelling' => false
            ]
        );
    }

    public function cancelAction()
    {
        $response = $this->getDefaultJsonResponse();

        $filter = $this->getFilterService()->getFilter()
            ->setOrganisationUnitId($this->getOrderService()->getActiveUser()->getOuList())
            ->setPage(1)
            ->setLimit('all');

        try {
            $ids = $this->params()->fromPost('orders');
            if (!is_array($ids) || empty($ids)) {
                throw new NotFound('No Orders provided');
            }

            $orders = $this->getOrderService()->getOrders($filter->setOrderIds($ids));
        } catch (NotFound $exception) {
            return $response->setVariable('error', 'No Orders found');
        }

        return $this->cancelOrders(
            $response,
            $orders,
            $this->params()->fromPost('type'),
            $this->params()->fromPost('reason')
        );
    }

    protected function cancelOrders(JsonModel $response, OrderCollection $orders, $type, $reason)
    {
        $failedOrderIds = [];
        foreach ($orders as $order) {
            try {
                $this->cancelOrder($order, $type, $reason);
            } catch (Exception $exception) {
                $failedOrderIds[] = $order->getId();
                $this->logException($exception, 'error', __NAMESPACE__);
            }
        }

        if (!empty($failedOrderIds)) {
            return $response->setVariable(
                'error',
                'Failed to mark the following orders for cancellation: ' . implode(', ', $failedOrderIds)
            );
        }

        return $response->setVariable('cancelling', true);
    }

    protected function cancelOrder(Order $order, $type, $reason)
    {
        $account = $this->getOrderService()->getAccountService()->fetch($order->getAccountId());
        $status = OrderMapper::calculateOrderStatusFromCancelType($type);
        $cancel = $this->getCancelValue($order, $type, $reason);

        $this->getOrderService()->saveOrder(
            $order->setStatus($status)
        );

        $this->getOrderCanceller()->generateJob($account, $order, $cancel);
    }

    /**
     * @param Order $order
     * @param string $type
     * @param string $reason
     * @return CancelValue
     */
    protected function getCancelValue(Order $order, $type, $reason)
    {
        $items = [];
        foreach ($order->getItems() as $item) {
            $items[] = [
                'orderItemId' => $item->getId(),
                'sku' => $item->getItemSku(),
                'quantity' => $item->getItemQuantity(),
                'amount' => $item->getIndividualItemPrice(),
                'unitPrice' => 0.00,
            ];
        }

        return $this->getDi()->newInstance(
            CancelValue::class,
            [
                'type' => $type,
                'timestamp' => date(DateTime::FORMAT),
                'reason' => $reason,
                'items' => $items,
                'shippingAmount' => $order->getShippingPrice(),
            ]
        );
    }
} 