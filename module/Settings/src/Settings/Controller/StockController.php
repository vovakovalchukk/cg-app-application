<?php
namespace Settings\Controller;

use CG\Product\StockMode;
use CG\Settings\Product\Entity as ProductSettings;
use CG\Settings\Product\Service as ProductSettingsService;
use CG_UI\View\DataTable;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG\User\OrganisationUnit\Service as UserOUService;
use Settings\Controller\StockJsonController;
use Settings\Controller\Stock\AccountTableTrait;
use Settings\Module;
use Zend\Mvc\Controller\AbstractActionController;

class StockController extends AbstractActionController
{
    use AccountTableTrait;

    const ACCOUNT_SETTINGS_TABLE_TEMPLATE = 'Account Settings Table';
    const ROUTE = 'Stock';
    const ROUTE_URI = '/stock';

    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var ProductSettingsService */
    protected $productSettingsService;
    /** @var UserOUService */
    protected $userOUService;
    /** @var DataTable */
    protected $accountsTable;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        ProductSettingsService $productSettingsService,
        UserOUService $userOUService,
        DataTable $accountsTable
    ) {
        $this->setViewModelFactory($viewModelFactory)
            ->setProductSettingsService($productSettingsService)
            ->setUserOUService($userOUService)
            ->setAccountsTable($accountsTable);
    }

    public function indexAction()
    {
        $rootOu = $this->userOUService->getRootOuByActiveUser();
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

    protected function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }

    protected function setProductSettingsService(ProductSettingsService $productSettingsService)
    {
        $this->productSettingsService = $productSettingsService;
        return $this;
    }

    protected function setUserOUService(UserOUService $userOUService)
    {
        $this->userOUService = $userOUService;
        return $this;
    }

    protected function setAccountsTable(DataTable $accountsTable)
    {
        $this->accountsTable = $accountsTable;
        return $this;
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