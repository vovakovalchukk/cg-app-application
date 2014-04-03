<?php
namespace Settings;

use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;
use CG_UI\View\NavBar;
use Zend\Di\Di;
use CG_UI\Layout\ViewModelFactory;
use CG_UI\Links\RouteLink;
use Zend\Mvc\Router\SimpleRouteStack;

class Module
{
    use NavBar\ModuleItemsTrait;

    const TEMPLATE = 'settings/sidebar';
    const ROUTE = 'Channel Management';

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
        $this->renderSideBar($event, $viewModel);
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
        if ($sidebar->getTemplate() != static::TEMPLATE) {
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