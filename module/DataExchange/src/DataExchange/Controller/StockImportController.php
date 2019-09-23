<?php
namespace DataExchange\Controller;

use DataExchange\Schedule\Service;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG_UI\View\Prototyper\ViewModelFactory;
use Zend\Mvc\Controller\AbstractActionController;

class StockImportController extends AbstractActionController
{
    public const ROUTE = 'StockImport';

    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var JsonModelFactory */
    protected $jsonModelFactory;
    /** @var Service */
    protected $service;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        JsonModelFactory $jsonModelFactory,
        Service $service
    ) {
        $this->viewModelFactory = $viewModelFactory;
        $this->jsonModelFactory = $jsonModelFactory;
        $this->service = $service;
    }

    public function indexAction()
    {
        return $this->viewModelFactory->newInstance([
            'isHeaderBarVisible' => false,
            'subHeaderHide' => true,
            'stockImportSchedules' => $this->service->fetchStockImportsForActiveUser(),
            'stockTemplateOptions' => $this->service->fetchStockTemplateOptionsForActiveUser(),
            'actionOptions' => $this->service->getStockImportActionOptions(),
            'fromAccountOptions' => $this->service->fetchFtpAccountOptionsForActiveUser(),
        ]);
    }
}