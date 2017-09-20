<?php
namespace Reports\Controller;

use CG_UI\View\Filters\Service as UIFiltersService;
use CG_UI\View\Prototyper\ViewModelFactory;
use Orders\Filter\Service as FilterService;
use Reports\Sales\Service as SalesService;
use Zend\Mvc\Controller\AbstractActionController;

class SalesController extends AbstractActionController
{
    const ROUTE_INDEX = '/sales';
    const FILTER_TYPE = 'orderSales';

    protected $viewModelFactory;
    /** @var  SalesService */
    protected $salesService;
    /** @var FilterService $filterService */
    protected $filterService;
    /** @var UIFiltersService $uiFiltersService */
    protected $uiFiltersService;

    public function __construct(ViewModelFactory $viewModelFactory, SalesService $service, FilterService $filterService, UIFiltersService $uiFilterService)
    {
        $this->viewModelFactory = $viewModelFactory;
        $this->salesService = $service;
        $this->filterService = $filterService;
        $this->uiFiltersService = $uiFilterService;
    }

    public function indexAction()
    {
        $view = $this->viewModelFactory->newInstance();
        $view->addChild($this->getFilterBar(), 'filters');
        return $view;
    }

    protected function getFilterBar()
    {
        $filterValues = $this->filterService->getPersistentFilter();
        $filters = $this->uiFiltersService->getFilters(static::FILTER_TYPE, $filterValues);
        return $filters->prepare();
    }
}
