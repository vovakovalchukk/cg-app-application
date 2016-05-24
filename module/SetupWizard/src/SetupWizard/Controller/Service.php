<?php
namespace SetupWizard\Controller;

use CG_UI\View\Prototyper\ViewModelFactory;
use CG_UI\View\Helper\NavigationMenu;
use Zend\View\Model\ViewModel;

class Service
{
    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var NavigationMenu */
    protected $navigationMenu;

    public function __construct(ViewModelFactory $viewModelFactory, NavigationMenu $navigationMenu)
    {
        $this->setViewModelFactory($viewModelFactory)
            ->setNavigationMenu($navigationMenu);
    }

    public function getSetupView($heading, $body, $footer = null)
    {
        $view = $this->getSetupLayoutView();
        if ($heading instanceof ViewModel) {
            $view->addChild($heading, 'heading');
        } else {
            $view->setVariable('heading', $heading);
        }
        if ($body instanceof ViewModel) {
            $view->addChild($body, 'body');
        } else {
            $view->setVariable('body', $body);
        }
        if (!$footer) {
            return $view;
        }
        if ($footer instanceof ViewModel) {
            $view->addChild($footer, 'footer');
        } else {
            $view->setVariable('footer', $footer);
        }
        return $view;
    }

    public function getSetupLayoutView()
    {
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('setup-wizard/layout/layout')
            ->setVariable('isNavBarVisible', false)
            ->setVariable('isHeaderBarVisible', false);

        return $view;
    }

    public function getFirstStepUri()
    {
        return $this->navigationMenu->getFirstPageUri();
    }

    protected function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }


    protected function setNavigationMenu(NavigationMenu $navigationMenu)
    {
        $navigationMenu->__invoke('setup-navigation');
        $this->navigationMenu = $navigationMenu;
        return $this;
    }
}
