<?php
namespace Products\Controller;

use CG\Channel\ItemCondition\Map as ChannelItemConditionMap;
use CG\Currency\Formatter as CurrencyFormatter;
use CG\Ebay\Site\Map as EbaySiteMap;
use CG\FeatureFlags\Lookup\Service as FeatureFlagsService;
use CG\Locale\CurrencyCode;
use CG\Locale\DemoLink;
use CG\Locale\Length as LocaleLength;
use CG\Locale\Mass as LocaleMass;
use CG\Locale\PhoneNumber;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Product\Client\Service as ProductClientService;
use CG\Settings\Product\Service as ProductSettingsService;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\ActiveUserInterface;
use CG_Access\Service as AccessService;
use CG_UI\View\BulkActions as BulkActions;
use CG_UI\View\DataTable;
use CG_UI\View\Prototyper\ViewModelFactory;
use Products\Listing\Channel\Service as ListingChannelService;
use Products\Product\BulkActions\Service as BulkActionsService;
use Products\Product\Category\Service as CategoryService;
use Products\Product\Listing\Service as ProductListingService;
use Products\Product\Service as ProductService;
use Products\Product\TaxRate\Service as TaxRateService;
use Products\Stock\Settings\Service as StockSettingsService;
use Settings\Controller\Stock\AccountTableTrait as AccountStockSettingsTableTrait;
use Settings\Controller\StockController;
use Settings\ListingTemplate\Service as ListingTemplateService;
use Settings\PickList\Service as PickListService;
use Zend\I18n\Translator\Translator;
use Zend\Mvc\Controller\AbstractActionController;

class ProductsController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;
    use AccountStockSettingsTableTrait;

    const ROUTE_INDEX_URL = '/products';

    const STOCK_TAB_FEATURE_FLAG = 'Stock Tab Enabled';
    const PRE_FETCH_VARIATIONS_FEATURE_FLAG = 'Pre Fetch Variations Enabled';
    const COST_PRICE_FEATURE_FLAG = 'Product cost price';

    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var ProductService */
    protected $productService;
    /** @var ListingTemplateService */
    protected $listingTemplateService;
    /** @var BulkActionsService */
    protected $bulkActionsService;
    /** @var Translator */
    protected $translator;
    /** @var DataTable */
    protected $accountStockSettingsTable;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var FeatureFlagsService */
    protected $featureFlagService;
    /** @var StockSettingsService */
    protected $stockSettingsService;
    /** @var TaxRateService */
    protected $taxRateService;
    /** @var CategoryService */
    protected $categoryService;
    /** @var OrganisationUnitService */
    protected $organisationUnitService;
    /** @var ProductListingService */
    protected $productListingService;
    /** @var PickListService */
    protected $pickListService;
    /** @var ListingChannelService */
    protected $listingChannelService;
    /** @var AccessService */
    protected $accessService;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        ProductService $productService,
        BulkActionsService $bulkActionsService,
        Translator $translator,
        DataTable $accountStockSettingsTable,
        ActiveUserInterface $activeUserContainer,
        FeatureFlagsService $featureFlagService,
        StockSettingsService $stockSettingsService,
        TaxRateService $taxRateService,
        CategoryService $categoryService,
        OrganisationUnitService $organisationUnitService,
        ProductListingService $productListingService,
        PickListService $pickListService,
        ListingTemplateService $listingTemplateService,
        ListingChannelService $listingChannelService,
        AccessService $accessService
    ) {
        $this->viewModelFactory = $viewModelFactory;
        $this->productService = $productService;
        $this->bulkActionsService = $bulkActionsService;
        $this->translator = $translator;
        $this->accountStockSettingsTable = $accountStockSettingsTable;
        $this->activeUserContainer = $activeUserContainer;
        $this->featureFlagService = $featureFlagService;
        $this->stockSettingsService = $stockSettingsService;
        $this->taxRateService = $taxRateService;
        $this->categoryService = $categoryService;
        $this->organisationUnitService = $organisationUnitService;
        $this->productListingService = $productListingService;
        $this->pickListService = $pickListService;
        $this->listingTemplateService = $listingTemplateService;
        $this->listingChannelService = $listingChannelService;
        $this->accessService = $accessService;
    }

    public function indexAction()
    {
        $rootOuId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        $rootOu = $this->organisationUnitService->fetch($rootOuId);
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
            'listingTemplates' => $this->featureFlagService->featureEnabledForOu(
                ListingTemplateService::FEATURE_FLAG,
                $rootOuId,
                $rootOu
            ),
            'linkedProducts' => $this->featureFlagService->featureEnabledForOu(
                ProductClientService::FEATURE_FLAG_LINKED_PRODUCTS,
                $rootOuId,
                $rootOu
            ),
            'createProducts' => $this->featureFlagService->featureEnabledForOu(
                ProductClientService::FEATURE_FLAG_CREATE_PRODUCTS,
                $rootOuId,
                $rootOu
            ),
            'stockTabEnabled' => $this->featureFlagService->featureEnabledForOu(
                static::STOCK_TAB_FEATURE_FLAG,
                $rootOuId,
                $rootOu
            ),
            'pickLocations' => $this->featureFlagService->featureEnabledForOu(
                PickListService::FEATURE_FLAG_PICK_LOCATIONS,
                $rootOuId,
                $rootOu
            ),
            'preFetchVariations' => $this->featureFlagService->featureEnabledForOu(
                static::PRE_FETCH_VARIATIONS_FEATURE_FLAG,
                $rootOuId,
                $rootOu
            ),
            'poStockInAvailableEnabled' => $this->featureFlagService->featureEnabledForOu(
                ProductSettingsService::FEATURE_FLAG_PO_STOCK_IN_AVAILABLE,
                $rootOuId,
                $rootOu
            ),
            'lowStockThresholdEnabled' => $this->featureFlagService->featureEnabledForOu(
                StockController::FEATURE_FLAG_LOW_STOCK_THRESHOLD,
                $rootOuId,
                $rootOu
            ),
            'costPriceEnabled' => $this->featureFlagService->featureEnabledForOu(
                static::COST_PRICE_FEATURE_FLAG,
                $rootOuId,
                $rootOu
            ),
            'productSearchActive' => $this->listingChannelService->isProductSearchActive($rootOu),
            'productSearchActiveForVariations' => $this->listingChannelService->isProductSearchActiveForVariations($rootOu)
        ]));

        $view->setVariable('stockModeOptions', $this->stockSettingsService->getStockModeOptions());
        $view->setVariable('incPOStockInAvailableOptions', $this->stockSettingsService->getIncPOStockInAvailableOptions());
        $view->setVariable('taxRates', $this->taxRateService->getTaxRatesOptionsForOuWithDefaultsSelected($rootOu));
        $view->setVariable('ebaySiteOptions', EbaySiteMap::getIdToNameMap());
        $view->setVariable('conditionOptions', ChannelItemConditionMap::getCgConditions());
        $view->setVariable('categoryTemplateOptions', $this->categoryService->getTemplateOptions());
        $view->setVariable('defaultCurrency', $this->getDefaultCurrencyForRootOu($rootOu));
        $view->setVariable('listingCreationAllowed', $this->productListingService->isListingCreationAllowed());
        $view->setVariable('managePackageUrl', $this->productListingService->getManagePackageUrl());
        $locale = $this->activeUserContainer->getLocale();
        $view->setVariable('salesPhoneNumber', PhoneNumber::getForLocale($locale));
        $view->setVariable('demoLink', DemoLink::getForLocale($locale));
        $view->setVariable('showVAT', $this->productService->isVatRelevant());
        $view->setVariable('massUnit', LocaleMass::getForLocale($locale));
        $view->setVariable('lengthUnit', LocaleLength::getForLocale($locale));
        $view->setVariable('pickLocations', $this->pickListService->getPickListSettings($rootOuId)->getLocationNames());
        $view->setVariable('pickLocationValues', $this->pickListService->getPickListValues($rootOuId));
        $view->setVariable('listingTemplates', $this->getListingTemplateOptions());

        $this->addAccountStockSettingsTableToView($view);
        $this->addAccountStockSettingsEnabledStatusToView($view);
        return $view;
    }

    protected function getListingTemplateOptions(): array
    {
        $options = [];
        $listingTemplates = $this->productListingService->fetchListingTemplates();
        if (!$listingTemplates) {
            return $options;
        }

        foreach ($listingTemplates as $listingTemplate) {
            $options[] = [
                'name' => $listingTemplate->getName(),
                'value' => $listingTemplate->getId(),
                'id' => $listingTemplate->getId(),
            ];
        }
        return $options;
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
        if ($this->accessService->isReadOnly()) {
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

    protected function getDefaultCurrencyForRootOu(OrganisationUnit $rootOu): ?string
    {
        $currencyCode = CurrencyCode::getCurrencyCodeForLocale($this->activeUserContainer->getLocale());
        return (new CurrencyFormatter($rootOu))->getSymbol($currencyCode);
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
