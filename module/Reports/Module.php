<?php
namespace Reports;

use CG_UI\Layout\ViewModelFactory;
use Zend\Config\Factory as ConfigFactory;
use Zend\Di\Di;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;

class Module
{
    const PUBLIC_FOLDER = '/cg-built/messages/';
    const ROUTE = 'Messages';
    const SIDEBAR_TEMPLATE = 'reports/sidebar';

    public function onBootstrap(MvcEvent $event)
    {
        $eventManager = $event->getApplication()->getEventManager();
        $eventManager->attach(MvcEvent::EVENT_RENDER, [$this, 'layoutHandler']);
    }

    public function getConfig()
    {
        return ConfigFactory::fromFiles(
            glob(__DIR__ . '/config/*.config.php')
        );
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function layoutHandler(MvcEvent $event)
    {
        $viewModel = $event->getViewModel();
        if (!($viewModel instanceof ViewModel)) {
            return;
        }
        $this->renderSideBar($event, $viewModel);
    }

    /**
     * @param MvcEvent $event
     * @return ViewModel
     */
    protected function getSidebarViewModel(MvcEvent $event)
    {
        $di = $event->getApplication()->getServiceManager()->get(Di::class);
        $viewModelFactory = $di->get(ViewModelFactory::class);
        return $viewModelFactory->get('sidebar');
    }

    protected function renderSideBar(MvcEvent $event, ViewModel $layout)
    {
        $sidebar = $this->getSidebarViewModel($event);
        if ($sidebar->getTemplate() != static::SIDEBAR_TEMPLATE) {
            return;
        }

        $sidebar->setVariable('title', static::ROUTE);
    }
}
