<?php
namespace Settings;

use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;
use CG_UI\View\NavBar;

class Module
{
    use NavBar\ModuleItemsTrait;

    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $eventManager->attach(MvcEvent::EVENT_RENDER, array($this, 'layoutHandler'));
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ],
            ],
        ];
    }

    public function layoutHandler(MvcEvent $event)
    {
        $viewModel = $event->getViewModel();
        if (!($viewModel instanceof ViewModel)) {
            return;
        }
        $this->renderNavBar($event, $viewModel);
    }

    /**
     * @return NavBar\Item[]
     */
    protected function getNavBarItems()
    {
        return [
            new NavBar\Item('settings', 'Settings', 'Channel Management'),
        ];
    }
}