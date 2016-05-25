<?php
namespace SetupWizard\Controller;

use CG_UI\View\Prototyper\ViewModelFactory;
use CG_UI\View\Helper\NavigationMenu;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Model\ViewModel;

class Service
{
    /** @var ViewModelFactory */
    protected $viewModelFactory;
    /** @var NavigationMenu */
    protected $navigationMenu;
    /** @var ServiceLocatorInterface */
    protected $serviceLocator;

    public function __construct(
        ViewModelFactory $viewModelFactory,
        NavigationMenu $navigationMenu,
        ServiceLocatorInterface $serviceLocator
    ) {
        $this->setViewModelFactory($viewModelFactory)
            ->setNavigationMenu($navigationMenu)
            ->setServiceLocator($serviceLocator);
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
            $footer = $this->getSetupFooterView();
        }
        if ($footer instanceof ViewModel) {
            $view->addChild($footer, 'footer');
        } else {
            $view->setVariable('footer', $footer);
        }
        return $view;
    }

    protected function getSetupLayoutView()
    {
        $view = $this->viewModelFactory->newInstance();
        $view->setTemplate('setup-wizard/layout/layout')
            ->setVariable('isNavBarVisible', false)
            ->setVariable('isHeaderBarVisible', false);

        return $view;
    }

    protected function getSetupFooterView()
    {
        $nextStepUri = $this->getNextStepUri();
        if (!$nextStepUri) {
            return '';
        }
        $footer = $this->viewModelFactory->newInstance([
            'buttons' => [
                [
                    'value' => 'Next',
                    'id' => 'setup-wizard-next-button',
                    'class' => 'setup-wizard-next-button',
                    'disabled' => false,
                    'action' => $nextStepUri,
                ], [
                    'value' => 'Skip',
                    'id' => 'setup-wizard-skip-button',
                    'class' => 'setup-wizard-skip-button',
                    'disabled' => false,
                    'action' => $nextStepUri,
                ]
            ]
        ]);
        $footer->setTemplate('elements/buttons.mustache');
        return $footer;
    }

    public function getFirstStepUri()
    {
        return $this->navigationMenu->getFirstPageUri();
    }

    public function getNextStepUri()
    {
        $currentPage = $this->navigationMenu->getPageByRoute($this->getCurrentRoute());
        return $this->navigationMenu->getNextPageUri($currentPage);
    }

    protected function getCurrentRoute()
    {
        return $this->serviceLocator
            ->get('Application')
            ->getMvcEvent()
            ->getRouteMatch()
            ->getMatchedRouteName();
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

    protected function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }
}
