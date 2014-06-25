<?php
namespace Settings;

use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;
use CG_UI\View\NavBar;
use Zend\Di\Di;
use CG_UI\Layout\ViewModelFactory;
use Zend\Config\Factory as ConfigFactory;
use Zend\Mvc\Router\SimpleRouteStack;
use Zend\View\Renderer\PhpRenderer;

class Module
{
    const PUBLIC_FOLDER = '/channelgrabber/settings/';
    const ROUTE = 'Settings';
    const SUBHEADER_TEMPLATE = 'settings/sub-header';
    const SIDEBAR_TEMPLATE = 'settings/sidebar';

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
        $sidebar->setVariable('routes', $this->getSettingRoutes($event));
    }

    /**
     * @param MvcEvent $event
     * @return array
     */
    protected function getSettingRoutes(MvcEvent $event)
    {
        $links = [];
        $router = $event->getApplication()->getServiceManager()->get('config')['router']['routes'];
        if (! isset($router[static::ROUTE])) {
            return $links;
        }
        $routes = $router[static::ROUTE];

        foreach ($routes['child_routes'] as $groupName => $route) {
            if ($route['type'] == SimpleRouteStack::class) {
                continue;
            }
            $links[$groupName] = [
                'route' => static::ROUTE.'/'.$groupName,
                'child_routes' => [
                ]
            ];

            foreach ($route['child_routes'] as $routeName => $childRoute) {
                $links[$groupName]['child_routes'][$routeName] = static::ROUTE.'/'.$groupName.'/'.$routeName;
            }
        }
        return $links;
    }
}