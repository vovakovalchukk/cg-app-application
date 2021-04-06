<?php
namespace Products\Controller;

use CG\FeatureFlags\Service as FeatureFlagService;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\UsageCheck\Exception\Exceeded as UsageExceeded;
use CG_Access\UsageExceeded\Service as AccessUsageExceededService;
use CG_UI\View\BulkActions;
use CG_UI\View\DataTable;
use CG_UI\View\Filters\Service as UIFiltersService;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG_Usage\Exception\Exceeded;
use Products\Listing\BulkActions\Service as BulkActionsService;
use Products\Listing\Filter\Service as FilterService;
use Products\Listing\Service as ListingService;
use Products\Module;
use Zend\Mvc\Controller\AbstractActionController;

class ListingsController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;

    const ROUTE_INDEX = 'listingsImport';
    const ROUTE_INDEX_URL = '/listing/import';
    const FILTER_TYPE = 'listingsImport';

    protected $viewModelFactory;
    /** @var ListingService */
    protected $listingService;
    protected $bulkActionsService;
    protected $listingList;
    protected $filterService;
    protected $uiFiltersService;
    /** @var AccessUsageExceededService */
    protected $accessUsageExceededService;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        ListingService $listingService,
        BulkActionsService $bulkActionsService,
        DataTable $listingList,
        FilterService $filterService,
        UIFiltersService $uiFiltersService,
        AccessUsageExceededService $accessUsageExceededService
    ) {
        $this->setViewModelFactory($viewModelFactory)
            ->setListingService($listingService)
            ->setBulkActionsService($bulkActionsService)
            ->setListingList($listingList)
            ->setFilterService($filterService)
            ->setUIFiltersService($uiFiltersService);
        $this->accessUsageExceededService = $accessUsageExceededService;
    }

    public function indexAction()
    {
        $view = $this->getViewModelFactory()->newInstance();
        $bulkActions = $this->getBulkActionsService()->getListPageBulkActions();
        $this->amendBulkActions($bulkActions);
        $bulkAction = $this->getViewModelFactory()->newInstance()->setTemplate('products/listings/bulk-actions/index');
        $bulkAction->addChild($this->getRefreshButtonView(), 'refreshButton');
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

    protected function amendBulkActions(BulkActions $bulkActions)
    {
        if (!$this->usageExceeded() && $this->listingService->listingImportPermitted()) {
            return;
        }
        $actions = $bulkActions->getActions();
        foreach ($actions as $action) {
            $action->setEnabled(false);
        }
    }

    protected function getRefreshButtonView()
    {
        $disabled = false;
        if ($this->usageExceeded() || !$this->listingService->listingImportPermitted()) {
            $disabled = true;
        }
        $refresh = $this->getViewModelFactory()->newInstance([
            'buttons' => true,
            'value' => 'Download listings',
            'id' => 'refresh-button',
            'disabled' => $disabled,
            'icon' => 'sprite-refresh-14-black'
        ]);
        $refresh->setTemplate('elements/buttons.mustache');
        return $refresh;
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

    protected function usageExceeded(): bool
    {
        try {
            $this->accessUsageExceededService->checkUsage();
            return false;
        } catch (UsageExceeded $e) {
            return true;
        }
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
