<?php
namespace Redux\Controller;

use CG_UI\View\Prototyper\ViewModelFactory;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    const ROUTE_INDEX = 'Redux Index';

    /** @var ViewModelFactory */
    protected $viewModelFactory;

    public function __construct(
        ViewModelFactory $viewModelFactory
    ) {
        $this->viewModelFactory = $viewModelFactory;
    }

    public function indexAction(): ViewModel
    {
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('redux/index');
        $view->setVariable('isHeaderBarVisible', false);
        $view->setVariable('isSidebarPresent', false);
        return $view;
    }
}