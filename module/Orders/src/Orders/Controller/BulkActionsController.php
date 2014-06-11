<?php
namespace Orders\Controller;

use CG\Stdlib\Exception\Runtime\NotFound;
use Orders\Order\Exception\MultiException;
use Zend\Mvc\Controller\AbstractActionController;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG_UI\View\Prototyper\JsonModelFactory;
use Zend\View\Model\JsonModel;
use Orders\Order\Service as OrderService;
use CG\Order\Shared\Collection as OrderCollection;
use Orders\Controller\BulkActions\ExceptionInterface as Exception;
use Orders\Controller\BulkActions\RuntimeException;
use Orders\Controller\BulkActions\InvalidArgumentException;

class BulkActionsController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;

    const TYPE_ORDER_IDS = 'orderIds';
    const TYPE_FILTER_ID = 'filterId';

    protected $jsonModelFactory;
    protected $orderService;
    protected $typeMap = [
        self::TYPE_ORDER_IDS => 'getOrdersFromOrderIds',
        self::TYPE_FILTER_ID => 'getOrdersFromFilterId',
    ];

    public function __construct(JsonModelFactory $jsonModelFactory, OrderService $orderService)
    {
        $this->setJsonModelFactory($jsonModelFactory)->setOrderService($orderService);
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

    /**
     * @param $action
     * @return JsonModel
     */
    protected function getDefaultJsonResponse($action)
    {
        return $this->getJsonModelFactory()->newInstance(
            [
                $action => false
            ]
        );
    }

    protected function getOrderIds()
    {
        return (array) $this->params()->fromPost('orders', []);
    }

    protected function getFilterId()
    {
        return $this->params()->fromRoute('filterId', '');
    }

    protected function getOrdersFromOrderIds()
    {
        return $this->getOrderService()->getOrdersById(
            $this->getOrderIds()
        );
    }

    protected function getOrdersFromFilterId()
    {
        return $this->getOrderService()->getOrdersFromFilterId(
            $this->getFilterId()
        );
    }

    protected function performActionOnOrderIds($action, callable $callable)
    {
        return $this->performAction(static::TYPE_ORDER_IDS, $action, $callable);
    }

    protected function performActionOnFilterId($action, callable $callable)
    {
        return $this->performAction(static::TYPE_FILTER_ID, $action, $callable);
    }

    protected function performAction($type, $action, callable $callable)
    {
        if (!isset($this->typeMap[$type])) {
            throw new RuntimeException(
                'Unsupported Bulk Action Type - ' . $type
            );
        }

        $response = $this->getDefaultJsonResponse($action);
        try {
            $orders = $this->{$this->typeMap[$type]}();
            $callable($orders);
        } catch (Exception $exception) {
            return $response->setVariable('error', $exception->getMessage());
        } catch (NotFound $exception) {
            return $response->setVariable('error', 'No Orders found');
        } catch (MultiException $exception) {
            $failedOrderIds = [];
            foreach ($exception as $orderId => $orderException) {
                $failedOrderIds[] = $orderId;
            }

            return $response->setVariable(
                'error',
                'Failed to update the following orders: ' . implode(', ', $failedOrderIds)
            );
        }
        return $response->setVariable($action, true);
    }

    public function tagOrderIdsAction()
    {
        return $this->performActionOnOrderIds(
            'tagged',
            [$this, 'tagOrders']
        );
    }

    public function tagFilterIdAction()
    {
        return $this->performActionOnFilterId(
            'tagged',
            [$this, 'tagOrders']
        );
    }

    public function tagOrders(OrderCollection $orders)
    {
        $tagAction = $this->getTagAction();
        $this->getOrderService()->{$tagAction}(
            $this->getTag(),
            $orders
        );
    }

    protected function getTag()
    {
        $tag = trim($this->params()->fromPost('tag', ''));
        if (strlen($tag) == 0) {
            throw new InvalidArgumentException('No Tag provided');
        }
        return $tag;
    }

    protected function getTagAction()
    {
        $actionMap = [
            'append' => 'tagOrders',
            'remove' => 'unTagOrders',
        ];

        $action = $this->params()->fromRoute('tagAction', '');

        if (!isset($actionMap[$action])) {
            throw new InvalidArgumentException('Unsupported tag action');
        }

        return $actionMap[$action];
    }

    public function dispatchOrderIdsAction()
    {
        return $this->performActionOnOrderIds(
            'dispatching',
            [$this, 'dispatchOrders']
        );
    }

    public function dispatchFilterIdAction()
    {
        return $this->performActionOnFilterId(
            'dispatching',
            [$this, 'dispatchOrders']
        );
    }

    public function dispatchOrders(OrderCollection $orders)
    {
        $this->getOrderService()->dispatchOrders($orders);
    }

    public function archiveOrderIdsAction()
    {
        return $this->performActionOnOrderIds(
            'archived',
            [$this, 'archiveOrders']
        );
    }

    public function archiveFilterIdAction()
    {
        return $this->performActionOnFilterId(
            'archived',
            [$this, 'archiveOrders']
        );
    }

    public function archiveOrders(OrderCollection $orders)
    {
        $this->getOrderService()->archiveOrders($orders);
    }
}