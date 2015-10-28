<?php
namespace Settings\Controller\Stock;

use Settings\Controller\StockController;
use Settings\Controller\StockJsonController;
use Settings\Module as SettingsModule;
use Zend\View\Model\ViewModel;

trait AccountTableTrait
{
    /**
     * @return self
     */
    public function addAccountStockSettingsTableToView(ViewModel $view)
    {
        $this->prepareDataTable();
        $tableView = $this->getTablePartialView();
        $view->addChild($tableView, 'accountStockSettingsTableView');
        return $this;
    }

    protected function prepareDataTable()
    {
        $settings = $this->getAccountStockSettingsTable()->getVariable('settings');
        $settings->setSource(
            $this->url()->fromRoute(SettingsModule::ROUTE . '/' . StockController::ROUTE . '/' . StockJsonController::ROUTE_ACCOUNTS)
        );
        $settings->setTemplateUrlMap($this->mustacheTemplateMap('stockAccountList'));
        return $this;
    }

    protected function getTablePartialView()
    {
        $view = $this->getViewModelFactory()->newInstance();
        $view->setTemplate(StockController::ACCOUNT_SETTINGS_TABLE_TEMPLATE);
        $view->addChild($this->getAccountStockSettingsTable(), 'accountStockSettingsTable');
        return $view;
    }

    /**
     * @return \CG_UI\View\DataTable
     */
    abstract protected function getAccountStockSettingsTable();

    /**
     * @return \CG_UI\View\Prototyper\ViewModelFactory
     */
    abstract protected function getViewModelFactory();

    /*
     * These methods are available on controllers via plugins but are exposed via a __call() method
     * so we can't have abstract versions of them here
     */
    //abstract protected function url();
    //abstract protected function mustacheTemplateMap($templateMap = null);
}
