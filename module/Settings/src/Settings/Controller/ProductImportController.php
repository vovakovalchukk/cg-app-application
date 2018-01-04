<?php
namespace Settings\Controller;

use CG_UI\View\Prototyper\ViewModelFactory;
use Products\Product\Csv\Service as ProductCsvService;
use Settings\Module;
use Settings\ProductImport\Service;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ProductImportController extends AbstractActionController
{
    const ROUTE = 'Product Import';
    const ROUTE_IMPORT = 'Import';

    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var Service */
    protected $service;
    /** @var ProductCsvService */
    protected $productCsvService;

    public function __construct(ViewModelFactory $viewModelFactory, Service $service, ProductCsvService $productCsvService)
    {
        $this->viewModelFactory = $viewModelFactory;
        $this->service = $service;
        $this->productCsvService = $productCsvService;
    }

    public function indexAction()
    {
        $view = $this->viewModelFactory->newInstance([
            'saveUri' => $this->url()->fromRoute(Module::ROUTE.'/'.static::ROUTE.'/'.static::ROUTE_IMPORT),
            'isHeaderBarVisible' => false,
            'subHeaderHide' => true,
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
        $accountId = $this->getRequest()->getPost('accountId');
        $fileData = $this->getRequest()->getPost('listingFile');
        if (!$accountId || (int)$accountId < 1 || !$fileData) {
            throw new \InvalidArgumentException(__METHOD__ . ' must be passed a valid Account ID and CSV file data');
        }

        $this->productCsvService->importFromCsv($fileData, $accountId);
    }
}