<?php

namespace Reports\Controller;

use CG_UI\View\Prototyper\ViewModelFactory;
use Zend\Mvc\Controller\AbstractActionController;

class SalesController extends AbstractActionController
{
    const ROUTE_INDEX = '/sales';

    protected $viewModelFactory;

    public function __construct(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
    }

    public function indexAction()
    {
        $view = $this->viewModelFactory->newInstance();
        return $view;
    }
}
