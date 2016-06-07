<?php
namespace CG_Shopify\Controller;

use CG_UI\View\Prototyper\ViewModelFactory;
use Zend\Mvc\Controller\AbstractActionController;

class AccountController extends AbstractActionController
{
    /** @var ViewModelFactory $viewModelFactory */
    protected $viewModelFactory;

    public function __construct(ViewModelFactory $viewModelFactory)
    {
        $this->setViewModelFactory($viewModelFactory);
    }

    public function setupAction()
    {

    }

    /**
     * @return self
     */
    protected function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }
} 
