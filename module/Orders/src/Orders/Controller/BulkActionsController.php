<?php
namespace Orders\Controller;

use CG\Order\Shared\Collection as OrderCollection;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Template\Entity as Template;
use CG_UI\View\Prototyper\JsonModelFactory;
use Orders\Order\Service as OrderService;
use Orders\Controller\BulkActions\ExceptionInterface as Exception;
use Orders\Controller\BulkActions\InvalidArgumentException;
use Orders\Controller\BulkActions\RuntimeException;
use Orders\Order\Batch\Service as BatchService;
use Orders\Order\Exception\MultiException;
use Orders\Order\Invoice\Service as InvoiceService;
use Settings\Module as Settings;
use Settings\Controller\InvoiceController as InvoiceSettings;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class BulkActionsController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;

    const TYPE_ORDER_IDS = 'orderIds';
    const TYPE_FILTER_ID = 'filterId';

    protected $jsonModelFactory;
    protected $orderService;
    protected $invoiceService;
    protected $batchService;
    protected $typeMap = [
        self::TYPE_ORDER_IDS => 'getOrdersFromOrderIds',
        self::TYPE_FILTER_ID => 'getOrdersFromFilterId',
    ];

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        OrderService $orderService,
        InvoiceService $invoiceService,
        BatchService $batchService
    ) {
        $this
            ->setJsonModelFactory($jsonModelFactory)
            ->setOrderService($orderService)
            ->setInvoiceService($invoiceService)
            ->setBatchService($batchService);
    }

    public function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
    {
        $this->jsonModelFactory = $jsonModelFactory;
        return $this;
    }

    /**
     * @return JsonModelFactory
     */
    protected function getJsonModelFactory()
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
    protected function getOrderService()
    {
        return $this->orderService;
    }

    public function setInvoiceService(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
        return $this;
    }

    /**
     * @return InvoiceService
     */
    protected function getInvoiceService()
    {
        return $this->invoiceService;
    }

    public function setBatchService(BatchService $batchService)
    {
        $this->batchService = $batchService;
        return $this;
    }

    /**
     * @return BatchService
     */
    public function getBatchService()
    {
        return $this->batchService;
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
        $orderIds = $this->getOrderIds();
        if (empty($orderIds)) {
            throw new NotFound('No orderIds provided');
        }
        return $this->getOrderService()->getOrdersById($orderIds);
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
        } catch (NotFound $exception) {
            return $response->setVariable('error', 'No Orders found');
        } catch (Exception $exception) {
            return $response->setVariable('error', $exception->getMessage());
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

    public function invoiceOrderIdsAction()
    {
        try {
            $x = $this->invoiceOrders(
                $this->getOrdersFromOrderIds()
            );
            return $x;
            return $this->getJsonModelFactory()->newInstance(['r' => print_r($x, true)]);
        } catch (NotFound $exception) {
            $e = $exception->getPrevious();


            return $this->getJsonModelFactory()->newInstance([
                'response' => get_class($e),
                'code' => $e->getMessage(),
                'loc' => $e->getFile() . ' L' . $e->getLine(),
                'foo' => (string) $e->getRequest(),
                'request' => $e->getRequest()->getMethod() .' '. $e->getRequest()->getUrl(),
                'params' => print_r($e->getRequest()->getParams(), true),
                'body' => print_r($e->getRequest()->getBody(), true),
                'trace' => get_class_methods($e->getRequest())
            ]);
            return $this->redirect()->toRoute('Orders');
        }
    }

    public function invoiceFilterIdAction()
    {
        try {
            return $this->invoiceOrders(
                $this->getOrdersFromFilterId()
            );
        } catch (NotFound $exception) {
            return $this->redirect()->toRoute('Orders');
        }
    }

    public function previewInvoiceAction()
    {
        try {
            $orders = $this->getOrderService()->getPreviewOrder();
            $template = $this->getInvoiceService()->createTemplate(
                (array) json_decode($this->params()->fromPost('template'), true)
            );
            return $this->invoiceOrders($orders, $template);
        }  catch (NotFound $exception) {
            return $this->redirect()->toRoute(
                implode(
                    '/',
                    [
                        Settings::ROUTE,
                        InvoiceSettings::ROUTE,
                    ]
                )
            );
        }
    }

    public function invoiceOrders(OrderCollection $orders)
    {
        return $this->getInvoiceService()->getResponseFromOrderCollection($orders);
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

    public function batchesAction()
    {
        return $this->getJsonModelFactory()->newInstance(
            ["batches" => $this->getBatchService()->getBatches()]
        );
    }

    public function batchOrderIdsAction()
    {
        return $this->performActionOnOrderIds(
            'batched',
            [$this, 'batchOrders']
        );
    }

    public function batchFilterIdAction()
    {
        return $this->performActionOnFilterId(
            'batched',
            [$this, 'batchOrders']
        );
    }

    public function batchOrders(OrderCollection $orders)
    {
        $this->getBatchService()->create($orders);
    }

    public function unBatchOrderIdsAction()
    {
        return $this->performActionOnOrderIds(
            'unBatched',
            [$this, 'unBatchOrders']
        );
    }

    public function unBatchFilterIdAction()
    {
        return $this->performActionOnFilterId(
            'unBatched',
            [$this, 'unBatchOrders']
        );
    }

    public function unBatchOrders(OrderCollection $orders)
    {
        $this->getBatchService()->remove($orders);
    }

    public function cancelOrderIdsAction()
    {
        return $this->performActionOnOrderIds(
            'cancelling',
            [$this, 'cancelOrders']
        );
    }

    public function cancelFilterIdAction()
    {
        return $this->performActionOnFilterId(
            'cancelling',
            [$this, 'cancelOrders']
        );
    }

    public function cancelOrders(OrderCollection $orders)
    {
        $this->getOrderService()->cancelOrders(
            $orders,
            $this->params()->fromPost('type'),
            $this->params()->fromPost('reason')
        );
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