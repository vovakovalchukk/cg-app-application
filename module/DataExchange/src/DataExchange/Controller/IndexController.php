<?php
namespace DataExchange\Controller;

use CG_UI\View\Prototyper\ViewModelFactory;
use DataExchange\Module;
use Zend\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{
    /** @var ViewModelFactory */
    protected $viewModelFactory;

    public function __construct(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
    }

    public function indexAction()
    {
        // MIG-42 should redirect to the history page here
        return $this->redirect()->toRoute(Module::ROUTE . '/' . FtpAccountController::ROUTE);
    }
}