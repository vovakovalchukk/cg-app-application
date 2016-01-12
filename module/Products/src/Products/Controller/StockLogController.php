<?php
namespace Products\Controller;

use CG_UI\View\Filters\Service as UIFiltersService;
use CG_UI\View\Prototyper\ViewModelFactory;
use Products\Stock\Log\FilterManager;
use Products\Stock\Log\Service;
use Zend\Mvc\Controller\AbstractActionController;

class StockLogController extends AbstractActionController
{
    const ROUTE_PRODUCT_LOGS = 'Product Stock Logs';
    const FILTER_TYPE = 'productStockLogs';

    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var UIFiltersService */
    protected $uiFiltersService;
    /** @var FilterManager */
    protected $filterManager;
    /** @var Service */
    protected $service;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        UIFiltersService $uiFiltersService,
        FilterManager $filterManager,
        Service $service
    ) {
        $this->setViewModelFactory($viewModelFactory)
            ->setUiFiltersService($uiFiltersService)
            ->setFilterManager($filterManager)
            ->setService($service);
    }

    public function indexAction()
    {
        $productId = $this->params()->fromRoute('productId');
        $productDetails = $this->service->getProductDetails($productId);

        $view = $this->viewModelFactory->newInstance();
        $view->setVariable('isSidebarPresent', false)
            ->addChild($this->getFilterBar(), 'filters')
            ->setVariable('productDetails', $productDetails);
        return $view;
    }

    protected function getFilterBar()
    {
        $filterValues = $this->filterManager->getPersistentFilter();
        $filters = $this->uiFiltersService->getFilters(static::FILTER_TYPE, $filterValues);
        return $filters->prepare();
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
}
