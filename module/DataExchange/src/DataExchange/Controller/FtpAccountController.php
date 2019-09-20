<?php
namespace DataExchange\Controller;

use DataExchange\FtpAccount\Service;
use CG_UI\View\Prototyper\ViewModelFactory;
use Zend\Mvc\Controller\AbstractActionController;

class FtpAccountController extends AbstractActionController
{
    public const ROUTE = 'FtpAccount';

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
        return $this->viewModelFactory->newInstance([
            'isHeaderBarVisible' => false,
            'subHeaderHide' => true,
            'ftpAccounts' => $this->service->fetchAllForActiveUser(),
            'ftpAccountTypeOptions' => $this->service->getTypeOptions(),
            'ftpDefaultPorts' => $this->service->getDefaultPorts(),
        ]);
    }
}