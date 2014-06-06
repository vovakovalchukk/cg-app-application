<?php
namespace Orders\Controller;

use CG\Stdlib\Exception\Runtime\NotFound;
use Exception;
use Zend\Mvc\Controller\AbstractActionController;
use Orders\Order\Service as OrderService;
use Orders\Filter\Service as FilterService;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Status as OrderStatus;
use CG\Channel\Gearman\Generator\Order\Dispatch as OrderDispatcher;
use Zend\View\Model\JsonModel;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;

class DispatchController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;

    protected $orderService;
    protected $filterService;
    protected $orderDispatcher;
    protected $jsonModelFactory;

    public function __construct(
        OrderService $orderService,
        FilterService $filterService,
        OrderDispatcher $orderDispatcher,
        JsonModelFactory $jsonModelFactory
    ) {
        $this
            ->setOrderService($orderService)
            ->setFilterService($filterService)
            ->setOrderDispatcher($orderDispatcher)
            ->setJsonModelFactory($jsonModelFactory);
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

    public function setOrderDispatcher(OrderDispatcher $orderDispatcher)
    {
        $this->orderDispatcher = $orderDispatcher;
        return $this;
    }

    /**
     * @return OrderDispatcher
     */
    public function getOrderDispatcher()
    {
        return $this->orderDispatcher;
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
                'dispatching' => false
            ]
        );
    }

    public function jsonFilterAction()
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

        return $this->dispatchOrders($response, $orders);
    }

    public function jsonFilterIdAction()
    {
        $response = $this->getDefaultJsonResponse();

        $filterId = $this->params()->fromRoute('filterId');
        try {
            $orders = $this->getOrderService()->getOrdersFromFilterId(
                $filterId,
                'all',
                1,
                null,
                null
            );
        } catch (NotFound $exception) {
            return $response->setVariable('error', 'No Orders found');
        }

        return $this->dispatchOrders($response, $orders);
    }

    protected function dispatchOrders(JsonModel $response, OrderCollection $orders)
    {
        $failedOrderIds = [];
        foreach ($orders as $order) {
            try {
                $this->dispatchOrder($order);
            } catch (Exception $exception) {
                $failedOrderIds[] = $order->getId();
                $this->logException($exception, 'error', __NAMESPACE__);
            }
        }

        if (!empty($failedOrderIds)) {
            return $response->setVariable(
                'error',
                'Failed to mark the following orders for dispatch: ' . implode(', ', $failedOrderIds)
            );
        }

        return $response->setVariable('dispatching', true);
    }

    protected function dispatchOrder(Order $order)
    {
        $account = $this->getOrderService()->getAccountService()->fetch($order->getAccountId());

        $this->getOrderService()->saveOrder(
            $order->setStatus(OrderStatus::DISPATCHING)
        );

        $this->getOrderDispatcher()->generateJob($account, $order);
    }
} 