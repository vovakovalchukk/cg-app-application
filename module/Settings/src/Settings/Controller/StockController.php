<?php
namespace Settings\Controller;

use CG\FeatureFlags\Service as FeatureFlagsService;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Product\StockMode;
use CG\Settings\Product\Entity as ProductSettings;
use CG\Settings\Product\Service as ProductSettingsService;
use CG\User\OrganisationUnit\Service as UserOUService;
use CG_UI\View\DataTable;
use CG_UI\View\Prototyper\ViewModelFactory;
use Settings\Controller\Stock\AccountTableTrait;
use Settings\Module;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class StockController extends AbstractActionController
{
    use AccountTableTrait;

    const ACCOUNT_SETTINGS_TABLE_TEMPLATE = 'Account Settings Table';
    const ROUTE = 'Stock';
    const ROUTE_URI = '/stock';
    const FEATURE_FLAG_LOW_STOCK_THRESHOLD = 'Low stock threshold';

    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var ProductSettingsService */
    protected $productSettingsService;
    /** @var UserOUService */
    protected $userOUService;
    /** @var DataTable */
    protected $accountsTable;
    /** @var FeatureFlagsService */
    protected $featureFlagsService;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        ProductSettingsService $productSettingsService,
        UserOUService $userOUService,
        DataTable $accountsTable,
        FeatureFlagsService $featureFlagsService
    ) {
        $this->viewModelFactory = $viewModelFactory;
        $this->productSettingsService = $productSettingsService;
        $this->userOUService = $userOUService;
        $this->accountsTable = $accountsTable;
        $this->featureFlagsService = $featureFlagsService;
    }

    public function indexAction()
    {
        $rootOu = $this->userOUService->getRootOuByActiveUser();
        /** @var ProductSettings $productSettings */
        $productSettings = $this->productSettingsService->fetch($rootOu->getId());
        $saveUri = $this->url()->fromRoute(
            Module::ROUTE . '/' . static::ROUTE . '/' . StockJsonController::ROUTE_SAVE
        );
        $view = $this->viewModelFactory->newInstance();
        $view->setVariable('isHeaderBarVisible', false)
            ->setVariable('subHeaderHide', true)
            ->setVariable('saveUri', $saveUri)
            ->addChild($this->getDefaultStockModeSelect($productSettings), 'defaultStockModeSelect')
            ->setVariable('defaultStockLevel', (int)$productSettings->getDefaultStockLevel())
            ->addChild($this->getSaveButton(), 'saveButton');
        $this->addAccountStockSettingsTableToView($view);

        $this->addLowStockThresholdToView($rootOu, $view);

        return $view;
    }

    protected function getDefaultStockModeSelect(ProductSettings $productSettings)
    {
        $options = StockMode::getStockModesAsSelectOptions();
        $selectedMode = ($productSettings->getDefaultStockMode() ?: StockMode::LIST_ALL);
        foreach ($options as &$option) {
            if ($option['value'] == $selectedMode) {
                $option['selected'] = true;
                break;
            }
        }
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('elements/custom-select.mustache')
            ->setVariable('id', 'settings-stock-default-stock-mode')
            ->setVariable('name', 'defaultStockMode')
            ->setVariable('options', $options);
        return $view;
    }

    protected function getSaveButton()
    {
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('elements/buttons.mustache')
            ->setVariable('buttons', [
                ['id' => 'settings-stock-save-button', 'value' => 'Save']
            ]);
        return $view;
    }

    protected function addLowStockThresholdToView(OrganisationUnit $organisationUnit, ViewModel $view): void
    {
        if (!$this->featureFlagsService->isActive(static::FEATURE_FLAG_LOW_STOCK_THRESHOLD, $organisationUnit)) {
            return;
        }

        $view
            ->setVariable('lowStockThresholdValue',12)
            ->addChild($this->getLowStockThreshold(), 'lowStockThreshold');
    }

    protected function getLowStockThreshold()
    {
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('elements/toggle.mustache')
            ->setVariable('id', 'low-stock-threshold')
            ->setVariable('name', 'low-stock-threshold')
            ->setVariable('class', 'low-stock-threshold');
        return $view;
    }

    // Required by AccountTableTrait
    protected function getAccountStockSettingsTable()
    {
        return $this->accountsTable;
    }
    protected function getViewModelFactory()
    {
        return $this->viewModelFactory;
    }
}