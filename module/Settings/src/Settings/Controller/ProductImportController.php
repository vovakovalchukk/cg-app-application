<?php
namespace Settings\Controller;

use CG_UI\View\Prototyper\ViewModelFactory;
use Settings\ProductImport\Service;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ProductImportController extends AbstractActionController
{
    const ROUTE = 'Product Import';

    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var Service */
    protected $service;

    public function __construct(ViewModelFactory $viewModelFactory, Service $service)
    {
        $this->viewModelFactory = $viewModelFactory;
        $this->service = $service;
    }

    public function indexAction()
    {
        $view = $this->viewModelFactory->newInstance([
            'saveUri' => 'todo', // TODO
        ]);
        $view->addChild($this->getSalesAccountSelect(), 'accountSelect')
            ->addChild($this->getFileUpload(), 'fileUpload')
            ->addChild($this->getImportButton(), 'importButton');

        return $view;
    }

    protected function getSalesAccountSelect(): ViewModel
    {
        $accountOptions = $this->service->getSalesAccountsAsSelectOptions();
        $view = $this->viewModelFactory->newInstance([
            'id' => 'product-import-account-select',
            'name' => 'accountId',
            'options' => $accountOptions
        ]);
        $view->setTemplate('elements/custom-select.mustache');
        return $view;
    }

    protected function getFileUpload(): ViewModel
    {
        $view = $this->viewModelFactory->newInstance([
            'id' => 'product-import-file-upload',
            'name' => 'listingFile',
            'accept' => '.csv'
        ]);
        $view->setTemplate('elements/file-upload.mustache');
        return $view;
    }

    protected function getImportButton(): ViewModel
    {
        $view = $this->viewModelFactory->newInstance([
            'buttons' => [[
                'id' => 'product-import-button',
                'value' => 'Import'
            ]]
        ]);
        $view->setTemplate('elements/buttons.mustache');
        return $view;
    }

    public function importAction()
    {
        // TODO
    }
}