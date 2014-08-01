<?php
namespace Products\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use Orders\Order\Batch\Service as BatchService;
use Orders\Order\Timeline\Service as TimelineService;
use Orders\Filter\Service as FilterService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Order\Shared\Entity as OrderEntity;
use Orders\Order\BulkActions\Service as BulkActionsService;
use Orders\Module;
use DirectoryIterator;
use CG\Http\Rpc\Exception as RpcException;
use Orders\Order\FilterService as FiltersService;
use Orders\Order\StoredFilters\Service as StoredFiltersService;
use ArrayObject;
use CG\Stdlib\PageLimit;
use CG\Stdlib\OrderBy;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Order\Shared\Shipping\Conversion\Service as ShippingConversionService;

class ProductsController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;

    const FILTER_SHIPPING_METHOD_NAME = "shippingMethod";
    const FILTER_SHIPPING_ALIAS_NAME = "shippingAliasId";

    protected $orderService;
    protected $filterService;
    protected $batchService;
    protected $bulkActionsService;
    protected $jsonModelFactory;
    protected $viewModelFactory;
    protected $filtersService;
    protected $storedFiltersService;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        ViewModelFactory $viewModelFactory,
        FilterService $filterService,
        BatchService $batchService,
        BulkActionsService $bulkActionsService,
        FiltersService $filtersService,
        StoredFiltersService $storedFiltersService
    )
    {
        $this->setJsonModelFactory($jsonModelFactory)
            ->setViewModelFactory($viewModelFactory)
            ->setFilterService($filterService)
            ->setBatchService($batchService)
            ->setBulkActionsService($bulkActionsService)
            ->setFiltersService($filtersService)
            ->setStoredFiltersService($storedFiltersService);
    }

    public function indexAction()
    {
        $view = $this->getViewModelFactory()->newInstance();

        $bulkActions = $this->getBulkActionsService()->getBulkActions();
        $bulkAction = $this->getViewModelFactory()->newInstance()->setTemplate('orders/orders/bulk-actions/index');
        //$bulkAction->setVariable('isHeaderBarVisible', $this->getOrderService()->isFilterBarVisible());
        $bulkActions->addChild(
            $bulkAction,
            'afterActions'
        );

        //$view->addChild($bulkActions, 'bulkItems');
        //$view->addChild($this->getFilterBar(), 'filters');
        $view->addChild($this->getSimpleProductView(), 'product');
        $view->addChild($this->getStatusFilters(), 'statusFiltersSidebar');
       
        $view->addChild($this->getBatches(), 'batches');
        //$view->setVariable('isSidebarVisible', $this->getOrderService()->isSidebarVisible());
        //$view->setVariable('isHeaderBarVisible', $this->getOrderService()->isFilterBarVisible());
        //$view->setVariable('filterNames', $this->getOrderService()->getFilterService()->getFilterNames());
        return $view;
    }

    protected function getSimpleProductView()
    {
        $product = $this->getViewModelFactory()->newInstance([
            'title' => 'Nike',
            'SKU' => 'NKE',
            'id' => "deleteButton-",
            'available' => 5
        ]);
        $product->setTemplate('elements/simple-product.mustache');

        return $product;
    }

    protected function getStatusFilters()
    {
        $view = $this->getViewModelFactory()->newInstance(
            [
                'filters' => $this->getFiltersService()->getFilterConfig('stateFilters')
            ]
        );
        $view->setTemplate('orders/orders/sidebar/statusFilters');
        return $view;
    }

    protected function getBatches()
    {
        $view = $this->getViewModelFactory()->newInstance();
        $view->setTemplate('layout/sidebar/batches');
        $view->setVariable('batches', $this->getBatchService()->getBatches());
        return $view;
    }

    protected function getFilterBar()
    {
        $filterValues = $this->getFilterService()->getPersistentFilter();
        $filters = $this->getOrderService()->getFilterService()->getOrderFilters($filterValues);
        return $filters->prepare();
    }

    protected function getDetailsSidebar()
    {
        $sidebar = $this->getViewModelFactory()->newInstance();
        $sidebar->setTemplate('orders/orders/sidebar/navbar');

        $links = [
            'order-status-details' => 'Order Status',
            'bulk-actions' => 'Bulk Actions',
            'timeline' => 'Timeline',
            'order-alert' => 'Alert',
            'order-buyer-message' => 'Buyer Message',
            'addressInformation' => 'Address Information',
            'product-payment-table' => 'Payment Information',
            'order-notes' => 'Notes'

        ];
        $sidebar->setVariable('links', $links);

        return $sidebar;
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

        $filter = $this->getFilterService()->getFilter()
            ->setOrganisationUnitId($this->getOrderService()->getActiveUser()->getOuList())
            ->setPage($pageLimit->getPage())
            ->setLimit($pageLimit->getLimit())
            ->setOrderBy($orderBy->getColumn())
            ->setOrderDirection($orderBy->getDirection());

        $requestFilter = $this->params()->fromPost('filter', []);
        if (!isset($requestFilter['archived'])) {
            $requestFilter['archived'] = [false];
        }

        if (isset($requestFilter[static::FILTER_SHIPPING_ALIAS_NAME])) {
            $methodNames = $this->getShippingConversionService()->fromAliasIdsToMethodNames($requestFilter[static::FILTER_SHIPPING_ALIAS_NAME]);
            $requestFilter[static::FILTER_SHIPPING_METHOD_NAME] = $methodNames;
        }

        if (!empty($requestFilter)) {
            $filter = $this->getFilterService()->mergeFilters(
                $filter,
                $this->getFilterService()->getFilterFromArray($requestFilter)
            );
        }

        $this->getFilterService()->setPersistentFilter($filter);
        $this->updateColumnPositions();

        try {
            $orders = $this->getOrderService()->getOrders($filter);
            $this->mergeOrderDataWithJsonData(
                $pageLimit,
                $data,
                $this->getOrderService()->alterOrderTable($orders, $this->getEvent())
            );
        } catch (NotFound $exception) {
            // No Orders so ignoring
        }

        return $this->getJsonModelFactory()->newInstance($data);
    }

    public function jsonFilterIdAction()
    {
        $data = $this->getDefaultJsonData();
        $pageLimit = $this->getPageLimit();
        $orderBy = $this->getOrderBy();
        $filterId = $this->params()->fromRoute('filterId');

        $this->logDebugDump($filterId, "Filter id: ");

        $this->updateColumnPositions();

        try {
            $orders = $this->getOrderService()->getOrdersFromFilterId(
                $filterId,
                $pageLimit->getLimit(),
                $pageLimit->getPage(),
                $orderBy->getColumn(),
                $orderBy->getDirection()
            );

            $this->mergeOrderDataWithJsonData(
                $pageLimit,
                $data,
                $this->getOrderService()->alterOrderTable($orders, $this->getEvent())
            );
        } catch (NotFound $exception) {
            // No Orders so ignoring
        }

        return $this->getJsonModelFactory()->newInstance($data);
    }

    protected function setOrderService(OrderService $orderService)
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

    protected function setFilterService(FilterService $filterService)
    {
        $this->filterService = $filterService;
        return $this;
    }

    /**
     * @return FilterService
     */
    protected function getFilterService()
    {
        return $this->filterService;
    }

    protected function setJsonModelFactory(JsonModelFactory $jsonModelFactory)
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

    protected function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }

    /**
     * @return ViewModelFactory
     */
    protected function getViewModelFactory()
    {
        return $this->viewModelFactory;
    }

    protected function setBatchService(BatchService $batchService)
    {
        $this->batchService = $batchService;
        return $this;
    }

    /**
     * @return BatchService
     */
    protected function getBatchService()
    {
        return $this->batchService;

    }

    protected function setBulkActionsService(BulkActionsService $bulkActionsService)
    {
        $this->bulkActionsService = $bulkActionsService;
        return $this;
    }

    /**
     * @return BulkActionsService
     */
    protected function getBulkActionsService()
    {
        return $this->bulkActionsService;
    }

    protected function setFiltersService(FiltersService $filtersService)
    {
        $this->filtersService = $filtersService;
        return $this;
    }

    /**
     * @return FiltersService
     */
    protected function getFiltersService()
    {
        return $this->filtersService;
    }

    protected function setStoredFiltersService(StoredFiltersService $storedFiltersService)
    {
        $this->storedFiltersService = $storedFiltersService;
        return $this;
    }

    /**
      @return StoredFiltersService
    */
    protected function getStoredFiltersService()
    {
        return $this->storedFiltersService;
    }

    protected function setShippingConversionService(ShippingConversionService $shippingConversionService)
    {
        $this->shippingConversionService = $shippingConversionService;
        return $this;
    }

    protected function getShippingConversionService()
    {
        return $this->shippingConversionService;
    }
}