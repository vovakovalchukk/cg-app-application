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
        return $this->redirect()->toRoute(Module::ROUTE . '/' . HistoryController::ROUTE);
    }
}
