<?php
namespace Settings\Controller;

use CG\Product\StockMode;
use CG\Settings\Product\Entity as ProductSettings;
use CG\Settings\Product\Service as ProductSettingsService;
use CG_UI\View\Prototyper\ViewModelFactory;
use CG\User\OrganisationUnit\Service as UserOUService;
use Settings\Controller\StockJsonController;
use Settings\Module;
use Zend\Mvc\Controller\AbstractActionController;

class StockController extends AbstractActionController
{
    const ROUTE = 'Stock';
    const ROUTE_URI = '/stock';

    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var ProductSettingsService */
    protected $productSettingsService;
    /** @var UserOUService */
    protected $userOUService;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        ProductSettingsService $productSettingsService,
        UserOUService $userOUService
    ) {
        $this->setViewModelFactory($viewModelFactory)
            ->setProductSettingsService($productSettingsService)
            ->setUserOUService($userOUService);
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
}