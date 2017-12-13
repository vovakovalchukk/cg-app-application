<?php
namespace ShipStation\Controller;

use CG_UI\View\Prototyper\ViewModelFactory;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class AccountController extends AbstractActionController
{
    /** @var ViewModelFactory */
    protected $viewModelFactory;

    public function __construct(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
    }

    public function setupAction(): ViewModel
    {
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('ship-station/setup');
        
        return $view;
    }
}