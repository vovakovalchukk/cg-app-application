<?php
namespace Settings;

use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;
use CG_UI\View\NavBar;
use Zend\Di\Di;
use CG_UI\Layout\ViewModelFactory;
use Zend\Mvc\Router\SimpleRouteStack;

class Module
{
    use NavBar\ModuleServiceTrait;

    const PUBLIC_FOLDER = '/channelgrabber/settings/';
    const ROUTE = 'Channel Management';
    const SUBHEADER_TEMPLATE = 'settings/sub-header';
    const SIDEBAR_TEMPLATE = 'settings/sidebar';

    public function onBootstrap(MvcEvent $event)
    {
        $eventManager = $event->getApplication()->getEventManager();
        $eventManager->attach(MvcEvent::EVENT_RENDER, array($this, 'layoutHandler'));
        $eventManager->attach(MvcEvent::EVENT_RENDER, [$this->getNavBarService($event), 'appendNavBarItemsToNavBar']);
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
        $this->renderSideBar($event, $viewModel);
    }

    /**
     * @return NavBar\Item[]
     */
    protected function getNavBarItems()
    {
        return [
            new NavBar\Item('sprite-settings-18-white', 'Settings', 'Channel Management'),
        ];
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

        $router = $event->getApplication()->getServiceManager()->get('Router');
        if (!($router instanceof SimpleRouteStack) || !$router->hasRoute(static::ROUTE)) {
            return $links;
        }

        $route = $router->getRoute(static::ROUTE);
        if (!($route instanceof SimpleRouteStack)) {
            return $links;
        }

        foreach ($route->getRoutes() as $routeName => $childRoute) {
            $links[$routeName] = static::ROUTE . '/' . $routeName;
        }

        return $links;
    }
}