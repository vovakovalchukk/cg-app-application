<?php
namespace Settings\Controller;

use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use Settings\CreateListings\Csv\Importer as ListingsCsvImporter;
use Settings\CreateListings\Service;
use Settings\Module;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class CreateListingsController extends AbstractActionController
{
    const ROUTE = 'Create Listings';
    const ROUTE_IMPORT = 'Import';

    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var JsonModelFactory */
    protected $jsonModelFactory;
    /** @var Service */
    protected $service;
    /** @var ListingsCsvImporter */
    protected $listingsCsvImporter;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        JsonModelFactory $jsonModelFactory,
        Service $service,
        ListingsCsvImporter $listingsCsvImporter
    ) {
        $this->viewModelFactory = $viewModelFactory;
        $this->jsonModelFactory = $jsonModelFactory;
        $this->service = $service;
        $this->listingsCsvImporter = $listingsCsvImporter;
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
            'id' => 'listing-import-account-select',
            'name' => 'accountId',
            'options' => $accountOptions
        ]);
        $view->setTemplate('elements/custom-select.mustache');
        return $view;
    }

    protected function getFileUpload(): ViewModel
    {
        $view = $this->viewModelFactory->newInstance([
            'id' => 'listing-import-file-upload',
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
                'id' => 'listing-import-button',
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

        $this->listingsCsvImporter->importFromCsv($fileData, $accountId);
        return $this->jsonModelFactory->newInstance(['valid' => true, 'status' => 'The import has been started successfully']);
    }
}