<?php
namespace Products\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use Products\Listing\Service as ListingService;
use Products\Listing\BulkActions\Service as BulkActionsService;
use Products\Module;
use CG_UI\View\DataTable;
use CG_UI\View\Filters\Service as UIFiltersService;
use Products\Controller\ListingsJsonController;
use Products\Listing\Filter\Service as FilterService;

class ListingsController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;

    const ROUTE_INDEX = 'listingsImport';
    const ROUTE_INDEX_URL = '/listing/import';
    const FILTER_TYPE = 'listingsImport';

    protected $viewModelFactory;
    protected $listingService;
    protected $bulkActionsService;
    protected $listingList;
    protected $filterService;
    protected $uiFiltersService;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        ListingService $listingService,
        BulkActionsService $bulkActionsService,
        DataTable $listingList,
        FilterService $filterService,
        UIFiltersService $uiFiltersService
    ) {
        $this->setViewModelFactory($viewModelFactory)
            ->setListingService($listingService)
            ->setBulkActionsService($bulkActionsService)
            ->setListingList($listingList)
            ->setFilterService($filterService)
            ->setUIFiltersService($uiFiltersService);
    }

    public function indexAction()
    {
        $view = $this->getViewModelFactory()->newInstance();
        $bulkActions = $this->getBulkActionsService()->getListPageBulkActions();
        $bulkAction = $this->getViewModelFactory()->newInstance()->setTemplate('products/listings/bulk-actions/index');
        $bulkActions->addChild(
            $bulkAction,
            'afterActions'
        );
        $view->addChild($bulkActions, 'bulkItems');
        $view->addChild($this->getFilterBar(), 'filters');
        $bulkAction->setVariable('isHeaderBarVisible', $this->getListingService()->isFilterBarVisible());
        $view->setVariable('isHeaderBarVisible', $this->getListingService()->isFilterBarVisible());
        $view->addChild($this->getListingListView(), 'listings');
        $view->setVariable('filterNames', $this->getUIFiltersService()->getFilterNames(static::FILTER_TYPE));
        return $view;
    }

    protected function getListingListView()
    {
        $listingList = $this->getListingList();
        $settings = $listingList->getVariable('settings');
        $settings->setSource(
            $this->url()->fromRoute(Module::ROUTE . '/' . static::ROUTE_INDEX . '/' . ListingsJsonController::ROUTE_AJAX)
        );
        $settings->setTemplateUrlMap($this->mustacheTemplateMap('listingList'));
        return $listingList;
    }

    protected function getFilterBar()
    {
        $filterValues = $this->getFilterService()->getPersistentFilter();
        $filters = $this->getUIFiltersService()->getFilters(static::FILTER_TYPE, $filterValues);
        return $filters->prepare();
    }

    protected function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }

    protected function getViewModelFactory()
    {
        return $this->viewModelFactory;
    }

    protected function setBulkActionsService(BulkActionsService $bulkActionsService)
    {
        $this->bulkActionsService = $bulkActionsService;
        return $this;
    }

    protected function getBulkActionsService()
    {
        return $this->bulkActionsService;
    }

    protected function setListingService(ListingService $listingService)
    {
        $this->listingService = $listingService;
        return $this;
    }

    protected function getListingService()
    {
        return $this->listingService;
    }

    protected function setListingList(Datatable $listingList)
    {
        $this->listingList = $listingList;
        return $this;
    }

    protected function getListingList()
    {
        return $this->listingList;
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

    protected function setUIFiltersService(UIFiltersService $uiFiltersService)
    {
        $this->uiFiltersService = $uiFiltersService;
        return $this;
    }

    /**
     * @return UIFiltersService
     */
    protected function getUIFiltersService()
    {
        return $this->uiFiltersService;
    }
}
