<?php
namespace Orders\Controller;

use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Mvc\Controller\AbstractActionController;
use Orders\Order\Service as OrderService;
use CG_UI\View\Prototyper\JsonModelFactory;
use Zend\View\Model\JsonModel;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use Orders\Order\Exception\MultiException;

class CancelController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;

    protected $di;
    protected $orderService;
    protected $filterService;
    protected $orderCanceller;
    protected $jsonModelFactory;

    public function __construct(OrderService $orderService, JsonModelFactory $jsonModelFactory) {
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
                'cancelling' => false
            ]
        );
    }

    public function cancelAction()
    {
        $response = $this->getDefaultJsonResponse();

        try {
            $ids = $this->params()->fromPost('orders');
            if (!is_array($ids) || empty($ids)) {
                throw new NotFound();
            }

            $orders = $this->getOrderService()->getOrdersById($ids);
            $this->getOrderService()->cancelOrders(
                $orders,
                $this->params()->fromPost('type'),
                $this->params()->fromPost('reason')
            );
        } catch (NotFound $exception) {
            return $response->setVariable('error', 'No Orders found');
        } catch (MultiException $exception) {
            $failedOrderIds = [];
            foreach ($exception as $orderId => $orderException) {
                $failedOrderIds[] = $orderId;
            }

            return $response->setVariable(
                'error',
                'Failed to mark the following orders for cancellation: ' . implode(', ', $failedOrderIds)
            );
        }

        return $response->setVariable('cancelling', true);
    }
} 