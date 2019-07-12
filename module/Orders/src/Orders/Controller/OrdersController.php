<?php
namespace Orders\Controller;

use ArrayObject;
use CG\Locale\Mass;
use CG\Order\Service\Filter;
use CG\Order\Shared\Label\Service as OrderLabelService;
use CG\Order\Shared\OrderCounts\Storage\Api as OrderCountsApi;
use CG\Order\Shared\Shipping\Conversion\Service as ShippingConversionService;
use CG\Stdlib\DateTime as StdlibDateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stdlib\OrderBy;
use CG\Stdlib\PageLimit;
use CG\User\ActiveUserInterface;
use CG_UI\View\BulkActions as BulkActionsViewModel;
use CG_UI\View\Filters\Service as UIFiltersService;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG_Usage\Exception\Exceeded as UsageExceeded;
use CG_Usage\Service as UsageService;
use Orders\Controller\Helpers\Courier as CourierHelper;
use Orders\Controller\Helpers\OrdersTable as OrdersTableHelper;
use Orders\Filter\DisplayFilter;
use Orders\Filter\Service as FilterService;
use Orders\Order\Batch\Service as BatchService;
use Orders\Order\BulkActions\Action\Courier as CourierBulkAction;
use Orders\Order\BulkActions\Service as BulkActionsService;
use Orders\Order\BulkActions\SubAction\CourierManifest as CourierManifestBulkAction;
use Orders\Order\Service as OrderService;
use Orders\Order\StoredFilters\Service as StoredFiltersService;
use Orders\Order\TableService;
use Orders\Order\TableService\OrdersTableUserPreferences;
use Zend\I18n\View\Helper\CurrencyFormat;
use Zend\Mvc\Controller\AbstractActionController;

// todo - likely will need to be removed during TAC-450
use Settings\Invoice\Settings as InvoiceSettings;
use CG\Zend\Stdlib\Http\FileResponse as Response;

class OrdersController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;

    const ROUTE_INDEX_URL = '/orders';
    const ROUTE_IMAGES = 'Images';

    const FILTER_SHIPPING_METHOD_NAME = "shippingMethod";
    const FILTER_SHIPPING_ALIAS_NAME = "shippingAliasId";
    const FILTER_TYPE = "orders";

    /** @var UsageService $usageService */
    protected $usageService;
    /** @var CourierHelper $courierHelper */
    protected $courierHelper;
    /** @var OrderService $orderService */
    protected $orderService;
    /** @var FilterService $filterService */
    protected $filterService;
    /** @var BatchService $batchService */
    protected $batchService;
    /** @var BulkActionsService $bulkActionsService */
    protected $bulkActionsService;
    /** @var JsonModelFactory $jsonModelFactory */
    protected $jsonModelFactory;
    /** @var ViewModelFactory $viewModelFactory */
    protected $viewModelFactory;
    /** @var UIFiltersService $uiFiltersService */
    protected $uiFiltersService;
    /** @var StoredFiltersService $storedFiltersService */
    protected $storedFiltersService;
    /** @var ShippingConversionService $shippingConversionService */
    protected $shippingConversionService;
    /** @var OrderCountsApi $orderCountsApi */
    protected $orderCountsApi;
    /** @var ActiveUserInterface $activeUserContainer */
    protected $activeUserContainer;
    /** @var OrderLabelService */
    protected $orderLabelService;
    /** @var CurrencyFormat */
    protected $currencyFormat;
    /** @var TableService $tableService */
    protected $tableService;
    /** @var OrdersTableUserPreferences $orderTableUserPreferences */
    protected $orderTableUserPreferences;
    /** @var OrdersTableHelper $orderTableHelper */
    protected $orderTableHelper;
    /** @var InvoiceSettings $invoiceSettings */
    protected $invoiceSettings;

    public function __construct(
        UsageService $usageService,
        CourierHelper $courierHelper,
        OrderService $orderService,
        FilterService $filterService,
        BatchService $batchService,
        BulkActionsService $bulkActionsService,
        JsonModelFactory $jsonModelFactory,
        ViewModelFactory $viewModelFactory,
        UIFiltersService $uiFiltersService,
        StoredFiltersService $storedFiltersService,
        ShippingConversionService $shippingConversionService,
        OrderCountsApi $orderCountsApi,
        ActiveUserInterface $activeUserContainer,
        OrderLabelService $orderLabelService,
        CurrencyFormat $currencyFormat,
        TableService $tableService,
        OrdersTableUserPreferences $orderTableUserPreferences,
        OrdersTableHelper $orderTableHelper,
        InvoiceSettings $invoiceSettings
    ) {
        $this->currencyFormat = $currencyFormat;
        $this->usageService = $usageService;
        $this->courierHelper = $courierHelper;
        $this->orderService = $orderService;
        $this->filterService = $filterService;
        $this->batchService = $batchService;
        $this->bulkActionsService = $bulkActionsService;
        $this->jsonModelFactory = $jsonModelFactory;
        $this->viewModelFactory = $viewModelFactory;
        $this->uiFiltersService = $uiFiltersService;
        $this->storedFiltersService = $storedFiltersService;
        $this->shippingConversionService = $shippingConversionService;
        $this->orderCountsApi = $orderCountsApi;
        $this->activeUserContainer = $activeUserContainer;
        $this->tableService = $tableService;
        $this->orderTableUserPreferences = $orderTableUserPreferences;
        $this->orderTableHelper = $orderTableHelper;
        $this->invoiceSettings = $invoiceSettings;
    }

    public function indexAction()
    {
        $view = $this->viewModelFactory->newInstance();
        $ordersTable = $this->tableService->getOrdersTable();

        if ($searchTerm = $this->params()->fromQuery('search')) {
            $filterValues = [
                'searchTerm' => $searchTerm,
                'archived' => [true, false],
            ];
        } else {
            $filterValues = $this->filterService->getMapper()->toArray(
                $this->filterService->getPersistentFilter()->getFilter()
            );
        }
        if (isset($filterValues['purchaseDate']['from'])) {
            $filterValues['purchaseDate']['from'] = $this->dateFormatOutput($filterValues['purchaseDate']['from'], StdlibDateTime::FORMAT);
        }
        if (isset($filterValues['purchaseDate']['to'])) {
            $filterValues['purchaseDate']['to'] = $this->dateFormatOutput($filterValues['purchaseDate']['to'], StdlibDateTime::FORMAT);
        }
        $ordersTable->setVariable('filterValues', $filterValues);
        $settings = $ordersTable->getVariable('settings');
        $settings->setSource($this->url()->fromRoute('Orders/ajax'));
        $settings->setTemplateUrlMap($this->mustacheTemplateMap('orderList'));
        $view->addChild($ordersTable, 'ordersTable');

        $bulkActions = $this->getBulkActionsViewModel();
        $bulkAction = $this->viewModelFactory->newInstance()->setTemplate('orders/orders/bulk-actions/index');
        $bulkAction->setVariable('isHeaderBarVisible', $this->orderTableUserPreferences->isFilterBarVisible());
        $bulkActions->addChild(
            $bulkAction,
            'afterActions'
        );
        $view->addChild($bulkActions, 'bulkItems');

        $view->addChild($this->getFilterBar(), 'filters');
        $view->addChild($this->getStatusFilters(), 'statusFiltersSidebar');
        $view->addChild(
            $this->storedFiltersService->getStoredFiltersSidebarView(
                $this->orderTableUserPreferences->getUserPreference()
            ),
            'storedFiltersSidebar'
        );

        $view->addChild($this->getBatches(), 'batches');

        $view->setVariable('isSidebarVisible', $this->orderTableUserPreferences->isSidebarVisible());
        $view->setVariable('isHeaderBarVisible', $this->orderTableUserPreferences->isFilterBarVisible());
        $view->setVariable('filterNames', $this->uiFiltersService->getFilterNames(static::FILTER_TYPE));

        // todo - rework this in TAC-450
        $view->setVariable('pdfExportOptions', $this->getTemplateOptionsForPDFExport());

        return $view;
    }

    // todo - rework this in TAC-450
    protected function getTemplateOptionsForPDFExport()
    {
        $invoices = $this->invoiceSettings->getInvoices();
        $formatted = [];
        foreach ($invoices as $key => $value)
        {
            $formatted[$key] = [
                'id' => $key,
                'name' => $value->getName(),
                'favourite' => $key === 0
            ];
        }
        return $formatted;
    }

    // todo - rework this in TAC-450
    protected function pdfExportAction()
    {
        $orderIds = $this->params()->fromPost('orderIds');
        $templateIds = $this->params()->fromPost('templateIds');

        $mimeType = 'application/pdf';
        $fileName = 'dummy.pdf';
        $data = file_get_contents('dummy-template.pdf');

        return new Response(
            $mimeType,
            $fileName,
            $data
        );
    }

    protected function getStatusFilters()
    {
        $view = $this->viewModelFactory->newInstance(
            [
                'filters' => $this->uiFiltersService->getFilterConfig('stateFilters')
            ]
        );
        $view->setTemplate('orders/orders/sidebar/statusFilters');
        return $view;
    }

    protected function getBulkActionsViewModel()
    {
        $bulkActionsViewModel = $this->bulkActionsService->getBulkActions();
        $this->amendBulkActionsForCouriers($bulkActionsViewModel)
            ->amendBulkActionsForUsage($bulkActionsViewModel);

        return $bulkActionsViewModel;
    }

    protected function amendBulkActionsForCouriers(BulkActionsViewModel $bulkActionsViewModel)
    {
        $courierAccountsPresent = $this->courierHelper->hasCourierAccounts();
        $manifestableAccountsPresent = $this->courierHelper->hasManifestableCourierAccounts();
        if ($courierAccountsPresent && $manifestableAccountsPresent) {
            return $this;
        }
        foreach ($bulkActionsViewModel->getActions() as $action) {
            if (!($action instanceof CourierBulkAction)) {
                continue;
            }
            if (!$courierAccountsPresent) {
                $bulkActionsViewModel->getActions()->detach($action);
                break;
            }
            foreach ($action->getSubActions() as $subAction) {
                if (!($subAction instanceof CourierManifestBulkAction)) {
                    continue;
                }
                $action->getSubActions()->detach($subAction);
                break 2;
            }
        }

        return $this;
    }

    protected function amendBulkActionsForUsage(BulkActionsViewModel $bulkActionsViewModel)
    {
        try {
            $this->usageService->checkUsage();
        } catch (UsageExceeded $exception) {
            $actions = $bulkActionsViewModel->getActions();
            foreach ($actions as $action) {
                $action->setEnabled(false);
            }
        }
        return $this;
    }

    protected function getBatches()
    {
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('layout/sidebar/batches');
        $view->setVariable('batches', $this->batchService->getBatches());
        return $view;
    }

    protected function getFilterBar()
    {
        /** @var Filter $filterValues */
        if ($searchTerm = $this->params()->fromQuery('search')) {
            $filterValues = (new Filter())->setSearchTerm($searchTerm);
        } else {
            $filterValues = $this->filterService->getPersistentFilter();
        }
        $filters = $this->uiFiltersService->getFilters(static::FILTER_TYPE, $filterValues);
        return $filters->prepare();
    }

    protected function getDefaultJsonData()
    {
        return new ArrayObject(
            [
                'iTotalRecords' => 0,
                'iTotalDisplayRecords' => 0,
                'sEcho' => (int) $this->params()->fromPost('sEcho'),
                'Records' => [],
                'sFilterId' => null,
            ]
        );
    }

    protected function mergeOrderDataWithJsonData(PageLimit $pageLimit, ArrayObject $json, array $orderData)
    {
        $json['Records'] = $pageLimit->getPageData($orderData['orders']);
        $json['iTotalRecords'] = $json['iTotalDisplayRecords'] = $orderData['orderTotal'];
        $json['sFilterId'] = $orderData['filterId'];
        return $this;
    }

    protected function getPageLimit()
    {
        $pageLimit = new PageLimit();

        if ($this->params()->fromPost('iDisplayLength') > 0) {
            $pageLimit
                ->setLimit($this->params()->fromPost('iDisplayLength'))
                ->setPageFromOffset($this->params()->fromPost('iDisplayStart'));
        }

        return $pageLimit;
    }

    protected function getOrderBy()
    {
        $orderBy = new OrderBy();

        $orderByIndex = $this->params()->fromPost('iSortCol_0');
        if ($orderByIndex) {
            $orderBy
                ->setColumn($this->params()->fromPost('mDataProp_' . $orderByIndex))
                ->setDirection($this->params()->fromPost('sSortDir_0', 'asc'));
        }

        return $orderBy;
    }

    public function jsonFilterAction()
    {
        $data = $this->getDefaultJsonData();
        $pageLimit = $this->getPageLimit();
        $orderBy = $this->getOrderBy();

        $filter = $this->filterService->getFilter()
            ->setOrganisationUnitId($this->orderService->getActiveUser()->getOuList())
            ->setPage($pageLimit->getPage())
            ->setLimit($pageLimit->getLimit())
            ->setOrderBy($orderBy->getColumn())
            ->setOrderDirection($orderBy->getDirection());

        $requestFilter = $this->params()->fromPost('filter', []);
        $this->filterService->setPersistentFilter(
            new DisplayFilter(
                isset($requestFilter['more']) && is_array($requestFilter['more']) ? $requestFilter['more'] : [],
                $this->filterService->getFilterFromArray($requestFilter)
            )
        );

        $requestFilter = $this->filterService->addDefaultFiltersToArray($requestFilter);
        if (!empty($requestFilter)) {
            $filter = $this->filterService->mergeFilters(
                $filter,
                $this->filterService->getFilterFromArray($requestFilter)
            );
        }

        // Must localise the filter *after* persisting otherwise it'll happen again when its reloaded
        $this->localiseFilterData($filter);

        try {
            $orders = $this->orderService->getOrders($filter);
            $this->mergeOrderDataWithJsonData(
                $pageLimit,
                $data,
                $this->orderTableHelper->mapOrdersCollectionToArray($orders, $this->getEvent())
            );
        } catch (NotFound $exception) {
            // No Orders so ignoring
        }

        return $this->jsonModelFactory->newInstance($data);
    }

    protected function localiseFilterData(Filter $filter): void
    {
        if ($filter->getPurchaseDateFrom()) {
            $filter->setPurchaseDateFrom($this->dateFormatInput($filter->getPurchaseDateFrom()));
        }
        if ($filter->getPurchaseDateTo()) {
            $filter->setPurchaseDateTo($this->dateFormatInput($filter->getPurchaseDateTo()));
        }
        if ($filter->getWeightMin()) {
            $filter->setWeightMin(Mass::convert(
                $filter->getWeightMin(),
                Mass::getForLocale($this->activeUserContainer->getLocale())
            ));
        }
        if ($filter->getWeightMax()) {
            $filter->setWeightMax(Mass::convert(
                $filter->getWeightMax(),
                Mass::getForLocale($this->activeUserContainer->getLocale())
            ));
        }
    }

    public function jsonFilterIdAction()
    {
        $data = $this->getDefaultJsonData();
        $pageLimit = $this->getPageLimit();
        $orderBy = $this->getOrderBy();
        $filterId = $this->params()->fromRoute('filterId');

        $this->logDebugDump($filterId, "Filter id: ");

        try {
            $orders = $this->orderService->getOrdersFromFilterId(
                $filterId,
                $pageLimit->getLimit(),
                $pageLimit->getPage(),
                $orderBy->getColumn(),
                $orderBy->getDirection()
            );

            $this->mergeOrderDataWithJsonData(
                $pageLimit,
                $data,
                $this->orderTableHelper->mapOrdersCollectionToArray($orders, $this->getEvent())
            );
        } catch (NotFound $exception) {
            // No Orders so ignoring
        }

        return $this->jsonModelFactory->newInstance($data);
    }


    public function orderCountsAjaxAction()
    {
        $organisationUnitId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        $entity = $this->orderCountsApi->fetch($organisationUnitId);
        $data = $entity->toArray();
        return $this->jsonModelFactory->newInstance($data);
    }

    public function getDeferredColumnDataAction()
    {
        $orderIds = $this->params()->fromPost('orderIds');
        $ordersById = [];
        try {
            $labels = $this->courierHelper->getNonCancelledOrderLabelsForOrders($orderIds);
            foreach ($labels as $label) {
                $ordersById[$label->getOrderId()]['labelCreatedDate'] = $label->getCreated();
            }
        } catch (NotFound $exception) {
            // No Orders so ignoring
        }

        return $this->jsonModelFactory->newInstance(['newData' => $ordersById]);
    }

    public function updateColumnsAction()
    {
        $response = $this->jsonModelFactory->newInstance(['updated' => false]);

        $updatedColumns = $this->params()->fromPost('columns');
        if (!$updatedColumns) {
            return $response->setVariable('error', 'No columns provided');
        }

        $this->orderTableUserPreferences->updateUserPrefOrderColumns($updatedColumns);
        return $response->setVariable('updated', true);
    }

    public function updateColumnOrderAction()
    {
        $response = $this->jsonModelFactory->newInstance(['updated' => false]);
        $this->updateColumnPositions();
        return $response->setVariable('updated', true);
    }

    protected function updateColumnPositions()
    {
        $keyPrefix = 'mDataProp_';
        $columnPositions = [];
        $post = $this->params()->fromPost();
        foreach ($post as $key => $value) {
            if (strpos($key, $keyPrefix) === 0) {
                $columnPositions[$value] = substr($key, strlen($keyPrefix));
            }
        }
        $this->orderTableUserPreferences->updateUserPrefOrderColumnPositions($columnPositions);
    }

    public function imagesForOrdersAction()
    {
        $orderIds = $this->params()->fromPost('orders');
        if (!is_array($orderIds)) {
            throw new \Exception('Order Ids must be an array');
        }
        $imagesForOrders = $this->orderService->getImagesForOrders($orderIds);
        return $this->jsonModelFactory->newInstance($imagesForOrders);
    }

    public function setRecipientVatNumberAction()
    {
        $orderId = $this->params()->fromPost('order');
        $countryCode = $this->params()->fromPost('countryCode');
        $vatNumber = $this->params()->fromPost('vatNumber');

        $order = $this->orderService->getOrder($orderId);

        $response = $this->jsonModelFactory->newInstance(['success' => false]);
        try {

            $currencyFormatter = $this->currencyFormat;
            $this->orderService->saveRecipientVatNumberToOrder($order, $countryCode, $vatNumber);
            $response->setVariable('orderSubTotal', $currencyFormatter($order->getSubTotal(), $order->getCurrencyCode()));
            $response->setVariable('orderTotal', $currencyFormatter($order->getTotal(), $order->getCurrencyCode()));
            $response->setVariable('orderTax', $currencyFormatter($order->getTax(), $order->getCurrencyCode()));
            $response->setVariable('success', true);
        } catch (\Exception $e) {
            $response->setVariable('error', $e->getMessage());
        }
        return $response;
    }
}
