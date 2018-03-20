<?php
namespace Products\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use CG_UI\View\BulkActions as BulkActions;
use CG_UI\View\DataTable;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG_Usage\Service as UsageService;
use CG\User\ActiveUserInterface;
use Products\Product\Service as ProductService;
use Products\Product\BulkActions\Service as BulkActionsService;
use Products\Stock\Settings\Service as StockSettingsService;
use Settings\Controller\Stock\AccountTableTrait as AccountStockSettingsTableTrait;
use Zend\I18n\Translator\Translator;
use CG\FeatureFlags\Lookup\Service as FeatureFlagsService;
use CG\Product\Client\Service as ProductClientService;
use CG\Listing\Client\Service as ListingClientService;

class ProductsController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;
    use AccountStockSettingsTableTrait;

    const ROUTE_INDEX_URL = '/products';

    protected $viewModelFactory;
    protected $productService;
    protected $bulkActionsService;
    protected $translator;
    /** @var DataTable */
    protected $accountStockSettingsTable;
    /** @var ActiveUserInterface $activeUserContainer */
    protected $activeUserContainer;
    /** @var UsageService */
    protected $usageService;
    /** @var FeatureFlagsService */
    protected $featureFlagService;
    /** @var StockSettingsService */
    protected $stockSettingsService;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        ProductService $productService,
        BulkActionsService $bulkActionsService,
        Translator $translator,
        DataTable $accountStockSettingsTable,
        ActiveUserInterface $activeUserContainer,
        UsageService $usageService,
        FeatureFlagsService $featureFlagService,
        StockSettingsService $stockSettingsService
    ) {
        $this->viewModelFactory = $viewModelFactory;
        $this->productService = $productService;
        $this->bulkActionsService = $bulkActionsService;
        $this->translator = $translator;
        $this->accountStockSettingsTable = $accountStockSettingsTable;
        $this->activeUserContainer = $activeUserContainer;
        $this->usageService = $usageService;
        $this->featureFlagService = $featureFlagService;
        $this->stockSettingsService = $stockSettingsService;
    }

    public function indexAction()
    {
        $rootOuId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        $view = $this->viewModelFactory->newInstance();
        $view->addChild($this->getDetailsSidebar(), 'sidebarLinks');

        $bulkActions = $this->bulkActionsService->getListPageBulkActions();
        $this->amendBulkActionsForUsage($bulkActions);
        $bulkAction = $this->viewModelFactory->newInstance()->setTemplate('products/products/bulk-actions/index');
        $bulkActions->addChild(
            $bulkAction,
            'afterActions'
        );
        $view->addChild($bulkActions, 'bulkItems');
        $bulkAction->setVariable('isHeaderBarVisible', $this->productService->isFilterBarVisible());
        $view->addChild($this->getPaginationView(), 'pagination');
        $view->setVariable('isSidebarVisible', $this->productService->isSidebarVisible());
        $view->setVariable('isHeaderBarVisible', false);
        $view->setVariable('subHeaderHide', true);
        $view->setVariable('isAdmin', $this->activeUserContainer->isAdmin());
        $view->setVariable('searchTerm', $this->params()->fromQuery('search', ''));
        $view->setVariable('activeUserRootOu', $rootOuId);
        $view->setVariable('featureFlagJson', json_encode([
            'linkedProducts' => $this->featureFlagService->featureEnabledForOu(
                ProductClientService::FEATURE_FLAG_LINKED_PRODUCTS,
                $rootOuId
            ),
            'createListings' => $this->featureFlagService->featureEnabledForOu(
                ListingClientService::FEATURE_FLAG_CREATE_LISTINGS,
                $rootOuId
            )
        ]));
        // Dummy data to be replaced by LIS-140
        $taxRatesDummyData = [
            'GB' => [
                'GB1' => ['name' => 'Standard', 'rate' => 20, 'selected' => true],
                'GB2' => ['name' => 'Reduced', 'rate' => 5, 'selected' => false],
                'GB3' => ['name' => 'Exempt', 'rate' => 0, 'selected' => false],
            ],
            'FR' => [
                'FR1' => ['name' => 'Standard', 'rate' => 17, 'selected' => true],
                'FR2' => ['name' => 'Reduced', 'rate' => 7, 'selected' => false],
                'FR3' => ['name' => 'Exempt', 'rate' => 0, 'selected' => false],
            ],
        ];
        $view->setVariable('stockModeOptions', $this->stockSettingsService->getStockModeOptions());
        $view->setVariable('taxRates', $taxRatesDummyData);

        $this->addAccountStockSettingsTableToView($view);
        $this->addAccountStockSettingsEnabledStatusToView($view);
        return $view;
    }

    protected function getDetailsSidebar()
    {
        $sidebar = $this->viewModelFactory->newInstance();
        $sidebar->setTemplate('products/products/sidebar/navbar');

        $links = [];
        $sidebar->setVariable('links', $links);

        return $sidebar;
    }

    protected function amendBulkActionsForUsage(BulkActions $bulkActions)
    {
        if(!$this->usageService->hasUsageBeenExceeded()) {
            return $this;
        }

        $actions = $bulkActions->getActions();
        foreach($actions as $action) {
            $action->setEnabled(false);
        }
        return $this;
    }

    protected function getPaginationView()
    {
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('elements/pagination.mustache');
        $view->setVariable('id', 'product-pagination')
            ->setVariable('firstRecord', 0)
            ->setVariable('lastRecord', 0)
            ->setVariable('total', 0)
            ->setVariable('pageLinks', [['selected' => true, 'text' => '1']]);
        return $view;
    }

    protected function addAccountStockSettingsEnabledStatusToView($view)
    {
        $accountStockSettingsEnabledStatus = $this->productService->getAccountStockSettingsEnabledStatus();
        $view->setVariable('accountStockModesEnabled', $accountStockSettingsEnabledStatus);
    }

    // Required by AccountTableTrait
    protected function getViewModelFactory()
    {
        return $this->viewModelFactory;
    }

    // Required by AccountTableTrait
    protected function getAccountStockSettingsTable()
    {
        return $this->accountStockSettingsTable;
    }
}
