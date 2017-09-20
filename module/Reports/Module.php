<?php
namespace Reports;

use CG_UI\Layout\ViewModelFactory;
use Reports\Sales\Service;
use Zend\Config\Factory as ConfigFactory;
use Zend\Di\Di;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;

class Module
{
    const PUBLIC_FOLDER = '/cg-built/reports/';
    const ROUTE = 'Reports';
    const SIDEBAR_TEMPLATE = 'reports/sidebar';

    public function onBootstrap(MvcEvent $event)
    {
        $eventManager = $event->getApplication()->getEventManager();
        $eventManager->attach(MvcEvent::EVENT_RENDER, [$this, 'layoutHandler']);
        $eventManager->attach(MvcEvent::EVENT_RENDER, [$this, 'appendStylesheet']);
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
        $this->renderSideBar($event);
    }

    public function appendStylesheet(MvcEvent $e)
    {
        $serviceManager = $e->getApplication()->getServiceManager();
        $renderer = $serviceManager->get(PhpRenderer::class);
        $basePath = $serviceManager->get('viewhelpermanager')->get('basePath');
        $renderer->headLink()->appendStylesheet($basePath() . static::PUBLIC_FOLDER . 'css/default.css');
    }

    /**
     * @param MvcEvent $event
     * @return ViewModel
     */
    protected function getSidebarViewModel(MvcEvent $event)
    {
        $viewModelFactory = $this->getService($event, ViewModelFactory::class);
        return $viewModelFactory->get('sidebar');
    }

    protected function renderSideBar(MvcEvent $event)
    {
        $sidebar = $this->getSidebarViewModel($event);
        if ($sidebar->getTemplate() != static::SIDEBAR_TEMPLATE) {
            return;
        }

        /** @var Service $service */
        $service = $this->getService($event,Service::class);
        $sidebar->setVariable('channels', $service->getChannelsForActiveUser());
        $sidebar->setVariable('total', $service->getTotalFilter());
    }

    protected function getService(MvcEvent $event, $class)
    {
        $di = $event->getApplication()->getServiceManager()->get(Di::class);
        return $di->get($class);
    }
}
