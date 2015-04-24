<?php
namespace Orders\Controller;

use CG\Http\Exception\Exception3xx\NotModified;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Template\Entity as Template;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_Usage\Service as UsageService;
use CG_Usage\Exception\Exceeded as UsageExceeded;
use Orders\Order\Service as OrderService;
use Orders\Controller\BulkActions\InvalidArgumentException;
use Orders\Controller\BulkActions\RuntimeException;
use Orders\Order\Batch\Service as BatchService;
use Orders\Order\Exception\MultiException;
use Orders\Order\Invoice\Service as InvoiceService;
use Orders\Order\PickList\Service as PickListService;
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
    protected $pickListService;
    protected $batchService;
    protected $usageService;
    protected $typeMap = [
        self::TYPE_ORDER_IDS => 'getOrdersFromOrderIds',
        self::TYPE_FILTER_ID => 'getOrdersFromFilterId',
    ];

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        OrderService $orderService,
        InvoiceService $invoiceService,
        PickListService $pickListService,
        BatchService $batchService,
        UsageService $usageService
    ) {
        $this
            ->setJsonModelFactory($jsonModelFactory)
            ->setOrderService($orderService)
            ->setInvoiceService($invoiceService)
            ->setPickListService($pickListService)
            ->setBatchService($batchService)
            ->setUsageService($usageService);
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
     * @return PickListService
     */
    protected function getPickListService()
    {
        return $this->pickListService;
    }

    public function setPickListService(PickListService $pickListService)
    {
        $this->pickListService = $pickListService;
        return $this;
    }

    /**
     * @return BatchService
     */
    public function getBatchService()
    {
        return $this->batchService;
    }

    protected function getUsageService()
    {
        return $this->usageService;
    }

    protected function setUsageService(UsageService $usageService)
    {
        $this->usageService = $usageService;
        return $this;
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

    protected function getInvoiceProgressKey()
    {
        return $this->params()->fromPost('invoiceProgressKey', null);
    }

    protected function getOrdersFromOrderIds($orderBy = null, $orderDir = null)
    {
        $orderIds = $this->getOrderIds();
        if (empty($orderIds)) {
            throw new NotFound('No orderIds provided');
        }
        return $this->getOrderService()->getOrdersById($orderIds, 'all', 1, $orderBy, $orderDir);
    }

    protected function getOrdersFromFilterId($orderBy = null, $orderDir = null)
    {
        return $this->getOrderService()->getOrdersFromFilterId(
            $this->getFilterId(), 'all', 1, $orderBy, $orderDir
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

        $this->checkUsage();

        $response = $this->getDefaultJsonResponse($action);
        try {
            $orders = $this->{$this->typeMap[$type]}();
            $callable($orders);
        } catch (MultiException $exception) {
            $failedOrderIds = [];
            foreach ($exception as $orderId => $orderException) {
                if ($orderException instanceof NotModified) {
                    continue;
                }
                $failedOrderIds[] = $orderId;
            }
            if (!count($failedOrderIds)) {
                return $response;
            }

            throw new \Exception('Failed to update the following orders: ' . implode(', ', $failedOrderIds), 0, $exception);
        }
        return $response->setVariable($action, true);
    }

    public function invoiceOrderIdsAction($orderBy = null, $orderDir = 'ASC')
    {
        try {
            $orders = $this->getOrdersFromOrderIds($orderBy, $orderDir);
            $progressKey = $this->getInvoiceProgressKey();
            $invoices = $this->invoiceOrders($orders, null, $progressKey);
            register_shutdown_function([$this, 'markOrdersAsPrinted'], $orders);
            return $invoices;
        } catch (NotFound $exception) {
            return $this->redirect()->toRoute('Orders');
        }
    }

    public function invoiceOrderIdsBySkuAction()
    {
        return $this->invoiceOrderIdsAction('itemSku');
    }

    public function invoiceFilterIdAction($orderBy = null, $orderDir = 'ASC')
    {
        try {
            $progressKey = $this->getInvoiceProgressKey();
            return $this->invoiceOrders(
                $this->getOrdersFromFilterId($orderBy, $orderDir), null, $progressKey
            );
        } catch (NotFound $exception) {
            return $this->redirect()->toRoute('Orders');
        }
    }

    public function invoiceFilterIdBySkuAction()
    {
        return $this->invoiceFilterIdAction('itemSku');
    }

    public function previewInvoiceAction()
    {
        try {
            $orders = $this->getOrderService()->getPreviewOrder();
            $templateData = (array) json_decode($this->params()->fromPost('template'), true);
            if (!isset($templateData['name'])) {
                $templateData['name'] = 'Preview';
            }
            $template = $this->getInvoiceService()->createTemplate($templateData);
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

    public function markOrdersAsPrinted(OrderCollection $orderCollection)
    {
        $this->getInvoiceService()->markOrdersAsPrintedFromOrderCollection($orderCollection);
        return $this;
    }

    public function invoiceOrders(OrderCollection $orders, Template $template = null, $progressKey = null)
    {
        return $this->getInvoiceService()->getResponseFromOrderCollection($orders, $template, $progressKey);
    }

    public function checkInvoicePrintingAllowedAction()
    {
        $this->checkUsage();

        return $this->getJsonModelFactory()->newInstance(
            ["allowed" => true, "guid" => uniqid('', true)]
        );
    }

    public function checkInvoiceGenerationProgressAction()
    {
        $progressKey = $this->getInvoiceProgressKey();
        $count = $this->getInvoiceService()->checkInvoiceGenerationProgress($progressKey);
        return $this->getJsonModelFactory()->newInstance(
            ["progressCount" => $count]
        );
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

    public function pickListOrderIdsAction($orderBy = null, $orderDir = 'ASC')
    {
        try {
            $orders = $this->getOrdersFromOrderIds($orderBy, $orderDir);
            $progressKey = $this->getPickListProgressKey();
            return $this->pickListOrders($orders, $progressKey);
        } catch (NotFound $exception) {
            return $this->redirect()->toRoute('Orders');
        }
    }

    public function pickListFilterIdAction($orderBy = null, $orderDir = 'ASC')
    {
        try {
            $orders = $this->getOrdersFromFilterId($orderBy, $orderDir);
            $progressKey = $this->getPickListProgressKey();
            return $this->pickListOrders($orders, $progressKey);
        } catch (NotFound $exception) {
            return $this->redirect()->toRoute('Orders');
        }
    }

    public function pickListOrders(OrderCollection $orders, $progressKey = null)
    {
        return $this->getPickListService()->getResponseFromOrderCollection($orders, $progressKey);
    }

    public function toCsvOrderIdsAction($orderBy = null, $orderDir = 'ASC')
    {

    }

    public function toCsvFilterIdAction($orderBy, $orderDir = 'ASC')
    {

    }

    public function checkPickListPrintingAllowedAction()
    {
        return $this->checkInvoicePrintingAllowedAction();
    }

    public function checkPickListGenerationProgressAction()
    {
        $progressKey = $this->getPickListProgressKey();
        $count = $this->getPickListService()->checkPickListGenerationProgress($progressKey);
        return $this->getJsonModelFactory()->newInstance(
            ["progressCount" => $count]
        );
    }

    protected function getPickListProgressKey()
    {
        return $this->params()->fromPost('pickListProgressKey', null);
    }

    protected function checkUsage()
    {
        if ($this->getUsageService()->hasUsageBeenExceeded()) {
            throw new UsageExceeded();
        }
    }
}