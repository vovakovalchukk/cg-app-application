<?php
namespace Orders\Controller;

use CG\Stdlib\Exception\Runtime\NotFound;
use Orders\Order\Exception\MultiException;
use Zend\Mvc\Controller\AbstractActionController;
use Orders\Order\Service as OrderService;
use CG_UI\View\Prototyper\JsonModelFactory;
use Zend\View\Model\JsonModel;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Order\Shared\Collection as OrderCollection;

class DispatchController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;

    protected $orderService;
    protected $jsonModelFactory;

    public function __construct(
        OrderService $orderService,
        JsonModelFactory $jsonModelFactory
    ) {
        $this
            ->setOrderService($orderService)
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

        try {
            $ids = $this->params()->fromPost('orders');
            if (!is_array($ids) || empty($ids)) {
                throw new NotFound('No Orders provided');
            }

            $orders = $this->getOrderService()->getOrdersById($ids);
            return $this->cancelOrders($response, $orders);
        } catch (NotFound $exception) {
            return $response->setVariable('error', 'No Orders found');
        }
    }

    public function jsonFilterIdAction()
    {
        $response = $this->getDefaultJsonResponse();

        try {
            $orders = $this->getOrderService()->getOrdersFromFilterId(
                $this->params()->fromRoute('filterId')
            );

            return $this->cancelOrders($response, $orders);
        } catch (NotFound $exception) {
            return $response->setVariable('error', 'No Orders found');
        }
    }

    protected function cancelOrders(JsonModel $response, OrderCollection $orders)
    {
        try {
            $this->getOrderService()->dispatchOrders($orders);
        } catch (MultiException $exception) {
            $failedOrderIds = [];
            foreach ($exception as $orderId => $orderException) {
                $failedOrderIds[] = $orderId;
            }

            return $response->setVariable(
                'error',
                'Failed to mark the following orders for dispatch: ' . implode(', ', $failedOrderIds)
            );
        }

        return $response->setVariable('dispatching', true);
    }
} 