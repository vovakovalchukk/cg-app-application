<?php
namespace Reports\Controller;

use CG_UI\View\Filters\Service as UIFiltersService;
use CG_UI\View\Prototyper\ViewModelFactory;
use Orders\Filter\DisplayFilter;
use Orders\Filter\Service as FilterService;
use Zend\Mvc\Controller\AbstractActionController;
use Reports\Order\Service as ReportsOrderService;

class SalesController extends AbstractActionController
{
    const ROUTE_INDEX = '/sales';
    const FILTER_TYPE = 'orderSales';

    protected $viewModelFactory;
    /** @var FilterService $filterService */
    protected $filterService;
    /** @var UIFiltersService $uiFiltersService */
    protected $uiFiltersService;
    /** @var ReportsOrderService */
    protected $orderService;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        ReportsOrderService $orderService,
        FilterService $filterService,
        UIFiltersService $uiFilterService
    ) {
        $this->viewModelFactory = $viewModelFactory;
        $this->orderService = $orderService;
        $this->filterService = $filterService;
        $this->uiFiltersService = $uiFilterService;
    }

    public function indexAction()
    {
        $view = $this->viewModelFactory->newInstance();
        $view->setVariable('isFullPageContent', true);
        $view->addChild($this->getFilterBar(), 'filters');
        $view->addChild($this->getHideFiltersView(), 'hideFilters');
        return $view;
    }

    protected function getHideFiltersView()
    {
        $hideFilters = $this->viewModelFactory->newInstance();
        $hideFilters->setTemplate('orders/orders/bulk-actions/index');
        $hideFilters->setVariable('isHeaderBarVisible', true);
        return $hideFilters;
    }

    protected function getFilterBar()
    {
        // Set the default period as the last 7 days
        $filter = $this->orderService->buildOrderFilterFromArray(['purchaseDate' => [
            'period' => 'Last 7 days',
            'from' => '-7 days',
            'to' => '23:59'
        ]]);

        /** @var DisplayFilter $filterValues */
        $filterValues = $this->filterService->createDisplayFilter($filter);
        $filters = $this->uiFiltersService->getFilters(static::FILTER_TYPE, $filterValues);
        return $filters->prepare();
    }
}
