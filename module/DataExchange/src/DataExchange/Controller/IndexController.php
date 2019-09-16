<?php
namespace DataExchange\Controller;

use CG_UI\View\Prototyper\ViewModelFactory;
use DataExchange\Module;
use Zend\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{
    public const ROUTE_EXAMPLE = 'Example';

    /** @var ViewModelFactory */
    protected $viewModelFactory;

    public function __construct(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
    }

    public function indexAction()
    {
        // MIG-42 should redirect to the history page here
        return $this->redirect()->toRoute(Module::ROUTE . '/' . static::ROUTE_EXAMPLE);
    }

    public function exampleAction()
    {
        return $this->viewModelFactory->newInstance([
            'isHeaderBarVisible' => false,
            'subHeaderHide' => true
        ]);
    }
}