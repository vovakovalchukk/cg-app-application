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
use Settings\Controller\Stock\AccountTableTrait as AccountStockSettingsTableTrait;
use Zend\I18n\Translator\Translator;
use CG\FeatureFlags\Lookup\Service as FeatureFlagsService;

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

    public function __construct(
        ViewModelFactory $viewModelFactory,
        ProductService $productService,
        BulkActionsService $bulkActionsService,
        Translator $translator,
        DataTable $accountStockSettingsTable,
        ActiveUserInterface $activeUserContainer,
        UsageService $usageService,
        FeatureFlagsService $featureFlagService
    ) {
        $this->setViewModelFactory($viewModelFactory)
             ->setProductService($productService)
             ->setBulkActionsService($bulkActionsService)
             ->setTranslator($translator)
             ->setAccountStockSettingsTable($accountStockSettingsTable)
             ->setActiveUserContainer($activeUserContainer)
             ->setUsageService($usageService);
        $this->featureFlagService = $featureFlagService;
    }

    public function indexAction()
    {
        $rootOuId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        $view = $this->getViewModelFactory()->newInstance();
        $view->addChild($this->getDetailsSidebar(), 'sidebarLinks');

        $bulkActions = $this->getBulkActionsService()->getListPageBulkActions();
        $this->amendBulkActionsForUsage($bulkActions);
        $bulkAction = $this->getViewModelFactory()->newInstance()->setTemplate('products/products/bulk-actions/index');
        $bulkActions->addChild(
            $bulkAction,
            'afterActions'
        );
        $view->addChild($bulkActions, 'bulkItems');
        $bulkAction->setVariable('isHeaderBarVisible', $this->getProductService()->isFilterBarVisible());
        $view->addChild($this->getPaginationView(), 'pagination');
        $view->setVariable('isSidebarVisible', $this->getProductService()->isSidebarVisible());
        $view->setVariable('isHeaderBarVisible', false);
        $view->setVariable('subHeaderHide', true);
        $view->setVariable('isAdmin', $this->activeUserContainer->isAdmin());
        $view->setVariable('searchTerm', $this->params()->fromQuery('search', ''));
        $view->setVariable('activeUserRootOu', $rootOuId);
        $view->setVariable('featureFlagJson', json_encode([
            'linkedProducts' => $this->featureFlagService->featureEnabledForOu(
                \CG\Product\Client\Service::FEATURE_FLAG_LINKED_PRODUCTS,
                $rootOuId
            ),
            'createListings' => $this->featureFlagService->featureEnabledForOu(
                \CG\Listing\Client\Service::FEATURE_FLAG_CREATE_LISTINGS,
                $rootOuId
            )
        ]));

        $this->addAccountStockSettingsTableToView($view);
        $this->addAccountStockSettingsEnabledStatusToView($view);
        return $view;
    }

    protected function getDetailsSidebar()
    {
        $sidebar = $this->getViewModelFactory()->newInstance();
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
        $view = $this->getViewModelFactory()->newInstance();
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

    protected function setProductService(ProductService $productService)
    {
        $this->productService = $productService;
        return $this;
    }

    protected function getProductService()
    {
        return $this->productService;
    }

    protected function getTranslator()
    {
        return $this->translator;
    }

    protected function setTranslator(Translator $translator)
    {
        $this->translator = $translator;
        return $this;
    }

    protected function setAccountStockSettingsTable(DataTable $accountStockSettingsTable)
    {
        $this->accountStockSettingsTable = $accountStockSettingsTable;
        return $this;
    }

    /**
     * @return self
     */
    protected function setActiveUserContainer(ActiveUserInterface $activeUserContainer)
    {
        $this->activeUserContainer = $activeUserContainer;
        return $this;
    }

    protected function setUsageService(UsageService $usageService)
    {
        $this->usageService = $usageService;
        return $this;
    }

    // Required by AccountTableTrait
    protected function getAccountStockSettingsTable()
    {
        return $this->accountStockSettingsTable;
    }
}
