<?php
namespace Products\Controller;

use CG_UI\View\DataTable;
use CG_UI\View\Filters\Service as UIFiltersService;
use CG_UI\View\Prototyper\ViewModelFactory;
use Products\Module;
use Products\Stock\Log\FilterManager;
use Products\Stock\Log\Service;
use Zend\Mvc\Controller\AbstractActionController;

class StockLogController extends AbstractActionController
{
    const ROUTE_PRODUCT_LOGS = 'Product Stock Logs';
    const FILTER_PRODUCT_LOGS = 'productStockLogs';
    const MUSTACHE_PRODUCT_LOGS = 'productStockLogs';

    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var UIFiltersService */
    protected $uiFiltersService;
    /** @var FilterManager */
    protected $filterManager;
    /** @var Service */
    protected $service;
    /** @var DataTable */
    protected $dataTable;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        UIFiltersService $uiFiltersService,
        FilterManager $filterManager,
        Service $service,
        DataTable $dataTable
    ) {
        $this->setViewModelFactory($viewModelFactory)
            ->setUiFiltersService($uiFiltersService)
            ->setFilterManager($filterManager)
            ->setService($service)
            ->setDataTable($dataTable);
    }

    public function indexAction()
    {
        $productId = $this->params()->fromRoute('productId');
        $productDetails = $this->service->getProductDetails($productId);
        $this->configureDataTable($productId);

        $view = $this->viewModelFactory->newInstance();
        $view->setVariable('isSidebarPresent', false)
            ->addChild($this->getFilterBar($productDetails), 'filters')
            ->setVariable('filterNames', $this->getFilterNames())
            ->setVariable('productDetails', $productDetails)
            ->addChild($this->dataTable, 'stockLogTable');
        return $view;
    }

    protected function getFilterBar(array $productDetails)
    {
        $filter = $this->filterManager->getPersistentFilter();
        $this->filterManager->setFilterDefaults($filter);
        $uiFilters = $this->uiFiltersService->getFilters(static::FILTER_PRODUCT_LOGS, $filter);
        $this->service->setUiFilterOptions($uiFilters, $productDetails);
        return $uiFilters->prepare();
    }

    protected function getFilterNames()
    {
        $filterNames = $this->uiFiltersService->getFilterNames(static::FILTER_PRODUCT_LOGS);
        return array_combine($filterNames, $filterNames);
    }

    protected function configureDataTable($productId)
    {
        $settings = $this->dataTable->getVariable('settings');
        $settings->setSource(
            $this->url()->fromRoute(
                Module::ROUTE . '/' . static::ROUTE_PRODUCT_LOGS . '/' . StockLogJsonController::ROUTE_AJAX,
                ['productId' => $productId]
            )
        );
        $settings->setTemplateUrlMap($this->mustacheTemplateMap(static::MUSTACHE_PRODUCT_LOGS));
    }

    protected function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }

    protected function setUiFiltersService(UIFiltersService $uiFiltersService)
    {
        $this->uiFiltersService = $uiFiltersService;
        return $this;
    }

    protected function setFilterManager(FilterManager $filterManager)
    {
        $this->filterManager = $filterManager;
        return $this;
    }

    protected function setService(Service $service)
    {
        $this->service = $service;
        return $this;
    }

    protected function setDataTable(DataTable $dataTable)
    {
        $this->dataTable = $dataTable;
        return $this;
    }
}
