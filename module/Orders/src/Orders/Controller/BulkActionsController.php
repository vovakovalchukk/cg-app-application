<?php
namespace Orders\Controller;

use CG\Http\Exception\Exception3xx\NotModified;
use CG\Order\Service\Filter;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Zend\Stdlib\Http\FileResponse;
use CG\Template\Entity as Template;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_Usage\Service as UsageService;
use CG_Usage\Exception\Exceeded as UsageExceeded;
use Orders\Order\Service as OrderService;
use Orders\Controller\BulkActions\InvalidArgumentException;
use Orders\Controller\BulkActions\RuntimeException;
use Orders\Order\Batch\Service as BatchService;
use Orders\Order\BulkActions\OrdersToOperateOn;
use Orders\Order\Exception\MultiException;
use Orders\Order\Invoice\Service as InvoiceService;
use Orders\Order\PickList\Service as PickListService;
use Orders\Order\Csv\Service as CsvService;
use Settings\Module as Settings;
use Settings\Controller\InvoiceController as InvoiceSettings;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class BulkActionsController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;

    const TYPE_ORDER_IDS = 'orderIds';
    const TYPE_FILTER_ID = 'filterId';

    /** @var JsonModelFactory $jsonModelFactory */
    protected $jsonModelFactory;
    /** @var OrderService $orderService */
    protected $orderService;
    /** @var InvoiceService $invoiceService */
    protected $invoiceService;
    /** @var PickListService $pickListService */
    protected $pickListService;
    /** @var CsvService $csvService */
    protected $csvService;
    /** @var BatchService $batchService */
    protected $batchService;
    /** @var UsageService $usageService */
    protected $usageService;
    /** @var OrdersToOperateOn $ordersToOperatorOn */
    protected $ordersToOperatorOn;

    protected $typeMap = [
        self::TYPE_ORDER_IDS => 'getOrdersFromInput',
        self::TYPE_FILTER_ID => 'getOrdersFromFilterId',
    ];

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        OrderService $orderService,
        InvoiceService $invoiceService,
        PickListService $pickListService,
        CsvService $csvService,
        BatchService $batchService,
        UsageService $usageService,
        OrdersToOperateOn $ordersToOperatorOn
    ) {
        $this
            ->setJsonModelFactory($jsonModelFactory)
            ->setOrderService($orderService)
            ->setInvoiceService($invoiceService)
            ->setPickListService($pickListService)
            ->setCsvService($csvService)
            ->setBatchService($batchService)
            ->setUsageService($usageService)
            ->setOrdersToOperatorOn($ordersToOperatorOn);
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
     * @return CsvService
     */
    protected function getCsvService()
    {
        return $this->csvService;
    }

    /**
     * @param CsvService $csvService
     * @return $this
     */
    public function setCsvService(CsvService $csvService)
    {
        $this->csvService = $csvService;
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

    protected function getFilterId()
    {
        return $this->params()->fromRoute('filterId', '');
    }

    protected function getInvoiceProgressKey()
    {
        return $this->params()->fromPost('invoiceProgressKey', null);
    }

    protected function getOrdersFromInput($orderBy = null, $orderDir = null)
    {
        $input = $this->params()->fromPost();
        $ordersToOperatorOn = $this->ordersToOperatorOn;
        return $ordersToOperatorOn($input, $orderBy, $orderDir);
    }

    protected function getFilterFromInput($orderBy = null, $orderDir = null)
    {
        $input = $this->params()->fromPost();
        $ordersToOperatorOn = $this->ordersToOperatorOn;
        return $ordersToOperatorOn->buildFilterFromInput($input, $orderBy, $orderDir);
    }

    /**
     * @deprecated use getOrdersFromInput
     */
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

    /**
     * @deprecated
     */
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
            $response->setVariable('filterId', $orders->getFilterId());
            $outcome = $callable($orders);
            if (is_array($outcome)) {
                $response->setVariables(array_merge([$action => true], $outcome));
            } else {
                $response->setVariable($action, (is_bool($outcome) ? $outcome : true));
            }
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
        return $response;
    }

    protected function performPatchingAction($action, callable $callable)
    {
        $this->checkUsage();
        $response = $this->getDefaultJsonResponse($action);

        $input = $this->params()->fromPost();
        $filter = $this->ordersToOperatorOn->buildFilterFromInput($input);
        $callable($filter);

        $response->setVariable($action, true)
            ->setVariable('filterId', $filter->getId());
        return $response;
    }

    public function invoiceOrderIdsAction($orderBy = null, $orderDir = 'ASC')
    {
        try {
            $orders = $this->getOrdersFromInput($orderBy, $orderDir);
            $this->markOrdersAsPrinted($orders);
            return $this->invoiceOrders($orders, null, $this->getInvoiceProgressKey());
        } catch (NotFound $exception) {
            throw new \RuntimeException('No orders were found to generate invoices for');
        }
    }

    public function invoiceOrderIdsBySkuAction()
    {
        return $this->invoiceOrderIdsAction('itemSku');
    }

    public function emailInvoiceAction()
    {
        return $this->performActionOnOrderIds(
            'emailing',
            [$this, 'emailInvoices']
        );
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

    public function emailInvoiceFilterAction()
    {
        return $this->performActionOnFilterId(
            'emailing',
            [$this, 'emailInvoices']
        );
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

    public function emailInvoices(OrderCollection $orders)
    {
        $invoiceService = $this->getInvoiceService();
        if ($this->params()->fromPost('validate', false)) {
            return $invoiceService->getInvoiceStats($orders);
        }
        $invoiceService->emailInvoicesForCollection(
            $orders,
            filter_var($this->params()->fromPost('includePreviouslySent', false), FILTER_VALIDATE_BOOLEAN)
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
        $tagAction = $this->getTagAction();
        return $this->{$tagAction}();
    }

    /**
     *
     * @deprecated Use tagOrderIdsAction
     */
    public function tagFilterIdAction()
    {
        return $this->performActionOnFilterId(
            'tagged',
            [$this, 'tagOrders']
        );
    }

    protected function tagOrders()
    {
        return $this->performPatchingAction('tagged', [$this, 'tagOrdersByFilter']);
    }

    protected function tagOrdersByFilter(Filter $filter)
    {
        $this->orderService->tagOrdersByFilter($this->getTag(), $filter);
    }

    protected function unTagOrders()
    {
        return $this->performActionOnOrderIds(
            'tagged',
            [$this, 'doUnTagOrders']
        );
    }

    protected function doUnTagOrders(OrderCollection $orders)
    {
        $this->getOrderService()->unTagOrders(
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
            ["batches" => $this->getBatchService()->getBatches(null)]
        );
    }

    public function batchOrderIdsAction()
    {
        return $this->batchOrders();
    }

    /**
     * @deprecated Use batchOrderIdsAction()
     */
    public function batchFilterIdAction()
    {
        return $this->performActionOnFilterId(
            'batched',
            [$this, 'batchOrders']
        );
    }

    protected function batchOrders()
    {
        return $this->performPatchingAction('batched', [$this, 'batchOrdersByFilter']);
    }

    protected function batchOrdersByFilter(Filter $filter)
    {
        $this->batchService->createFromFilter($filter);
    }

    protected function areOrdersAssociatedWithAnyBatchAction()
    {
        $orderIds = $this->getRequest()->getPost('orders', []);

        $batchMap = [];
        if (! empty($orderIds)) {
            $batchMap = $this->batchService->areOrdersAssociatedWithAnyBatch($orderIds);
        }

        return $this->getJsonModelFactory()->newInstance(
            ["batchMap" => $batchMap]
        );
    }

    public function unBatchOrderIdsAction()
    {
        return $this->unBatchOrders();
    }

    /**
     * @deprecated Use batchOrderIdsAction()
     */
    public function unBatchFilterIdAction()
    {
        return $this->performActionOnFilterId(
            'unBatched',
            [$this, 'unBatchOrders']
        );
    }

    public function unBatchOrders()
    {
        return $this->performPatchingAction('unBatched', [$this, 'unBatchOrdersByFilter']);
    }

    protected function unBatchOrdersByFilter(Filter $filter)
    {
        $this->batchService->removeByFilter($filter);
    }

    public function payForOrderAction()
    {
        return $this->performActionOnOrderIds(
            'paying',
            [$this, 'payForOrder']
        );
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
        return $this->archiveOrders();
    }

    /**
     * @deprecated Use archiveOrderIdsAction()
     */
    public function archiveFilterIdAction()
    {
        return $this->performActionOnFilterId(
            'archived',
            [$this, 'archiveOrders']
        );
    }

    protected function archiveOrders()
    {
        return $this->performPatchingAction('archived', [$this, 'archiveOrdersByFilter']);
    }

    protected function archiveOrdersByFilter(Filter $filter)
    {
        $this->orderService->archiveOrdersByFilter($filter);
    }

    public function unarchiveOrderIdsAction()
    {
        return $this->performPatchingAction('archived', [$this, 'unarchiveOrdersByFilter']);
    }

    protected function unarchiveOrdersByFilter(Filter $filter)
    {
        $this->orderService->archiveOrdersByFilter($filter, false);
    }

    public function checkInvoicePrintingAllowedAction()
    {
        return $this->getUsageViewModel();
    }

    public function pickListOrderIdsAction($orderBy = null, $orderDir = 'ASC')
    {
        try {
            $orders = $this->getOrdersFromInput($orderBy, $orderDir);
            $progressKey = $this->getPickListProgressKey();
            return $this->getPickListService()->getResponseFromOrderCollection($orders, $progressKey);
        } catch (NotFound $exception) {
            return $this->redirect()->toRoute('Orders');
        }
    }

    public function pickListFilterIdAction($orderBy = null, $orderDir = 'ASC')
    {
        try {
            $orders = $this->getOrdersFromFilterId($orderBy, $orderDir);
            $progressKey = $this->getPickListProgressKey();
            return $this->getPickListService()->getResponseFromOrderCollection($orders, $progressKey);
        } catch (NotFound $exception) {
            return $this->redirect()->toRoute('Orders');
        }
    }

    public function toCsvOrderIdsAction($orderBy = null, $orderDir = 'ASC')
    {
        try {
            $filter = $this->getFilterFromInput($orderBy, $orderDir);
            $csv = $this->getCsvService()->generateCsvFromFilterForOrdersAndItems($filter);
            return new FileResponse(CsvService::MIME_TYPE, CsvService::FILENAME, (string) $csv);
        } catch (NotFound $exception) {
            return $this->redirect()->toRoute('Orders');
        }
    }

    public function toCsvFilterIdAction($orderBy, $orderDir = 'ASC')
    {
        try {
            $orders = $this->getOrdersFromFilterId($orderBy, $orderDir);
            $progressKey = $this->getToCsvProgressKey();
            $csv = $this->getCsvService()->generateCsvForOrdersAndItems($orders, $progressKey);
            return new FileResponse(CsvService::MIME_TYPE, CsvService::FILENAME, (string) $csv);
        } catch (NotFound $exception) {
            return $this->redirect()->toRoute('Orders');
        }
    }

    public function toCsvOrderDataOnlyOrderIdsAction($orderBy = null, $orderDir = 'ASC')
    {
        try {
            $filter = $this->getFilterFromInput($orderBy, $orderDir);
            $csv = $this->getCsvService()->generateCsvFromFilterForOrders($filter);
            return new FileResponse(CsvService::MIME_TYPE, CsvService::FILENAME, (string) $csv);
        } catch (NotFound $exception) {
            return $this->redirect()->toRoute('Orders');
        }
    }

    public function toCsvOrderDataOnlyFilterIdAction($orderBy = null, $orderDir = 'ASC')
    {
        try {
            $orders = $this->getOrdersFromFilterId($orderBy, $orderDir);
            $progressKey = $this->getToCsvProgressKey();
            $csv = $this->getCsvService()->generateCsvForOrders($orders, $progressKey);
            return new FileResponse(CsvService::MIME_TYPE, CsvService::FILENAME, (string) $csv);
        } catch (NotFound $exception) {
            return $this->redirect()->toRoute('Orders');
        }
    }

    public function checkCsvGenerationAllowedAction()
    {
        return $this->getUsageViewModel();
    }

    public function checkCsvGenerationProgressAction()
    {
        $progressKey = $this->getToCsvProgressKey();
        $count = $this->getCsvService()->checkToCsvGenerationProgress($progressKey);
        return $this->getJsonModelFactory()->newInstance(
            ["progressCount" => $count]
        );
    }

    public function checkPickListPrintingAllowedAction()
    {
        return $this->getUsageViewModel();
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

    protected function getToCsvProgressKey()
    {
        return $this->params()->fromPost('toCsvProgressKey', null);
    }

    protected function getUsageViewModel()
    {
        $this->checkUsage();

        return $this->getJsonModelFactory()->newInstance(
            ["allowed" => true, "guid" => uniqid('', true)]
        );
    }

    protected function checkUsage()
    {
        if ($this->getUsageService()->hasUsageBeenExceeded()) {
            throw new UsageExceeded();
        }
    }

    public function saveFilterAction()
    {
        // Getting the orders will trigger the 'OrdersToOperatorOn' invokable which will save the filter
        $orders = $this->getOrdersFromInput();
        return $this->getJsonModelFactory()->newInstance(['filterId' => $orders->getFilterId()]);
    }

    protected function setOrdersToOperatorOn(OrdersToOperateOn $ordersToOperatorOn)
    {
        $this->ordersToOperatorOn = $ordersToOperatorOn;
        return $this;
    }
}
