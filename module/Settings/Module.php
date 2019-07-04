<?php
namespace Settings;

use CG_UI\Layout\ViewModelFactory;
use Zend\Config\Factory as ConfigFactory;
use Zend\Di\Di;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;

class Module
{
    const PUBLIC_FOLDER = '/cg-built/settings/';
    const ROUTE = 'Settings';
    const SUBHEADER_TEMPLATE = 'settings/sub-header';
    const SIDEBAR_TEMPLATE = 'settings/sidebar';
    const SESSION_KEY = 'OHSettings';

    public function onBootstrap(MvcEvent $event)
    {
        $eventManager = $event->getApplication()->getEventManager();
        $eventManager->attach(MvcEvent::EVENT_RENDER, array($this, 'layoutHandler'));
        $eventManager->attach(MvcEvent::EVENT_RENDER, [$this, 'appendStylesheet']);
    }

    public function appendStylesheet(MvcEvent $e)
    {
        $serviceManager = $e->getApplication()->getServiceManager();
        $renderer = $serviceManager->get(PhpRenderer::class);
        $basePath = $serviceManager->get('viewhelpermanager')->get('basePath');
        $renderer->headLink()->appendStylesheet($basePath() . static::PUBLIC_FOLDER . 'css/default.css');
    }

    public function getConfig()
    {
        return ConfigFactory::fromFiles(
            glob(__DIR__ . '/config/*.config.php')
        );
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