<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use CG\Http\Exception as HttpException;
use Zend\ModuleManager\ModuleManager;
use Zend\ModuleManager\ModuleEvent;
use CG\UI\Links\RoutesInterface;
use CG\UI\Links\NavigationRoutesInterface;
use Zend\Mvc\Router\RouteMatch;
use Zend\View\Model\ViewModel;

class Module
{
    protected $routes = array();
    protected $navigationRoutes = array();

    public function init(ModuleManager $moduleManager)
    {
        $eventManager = $moduleManager->getEventManager();
        $eventManager->attach(ModuleEvent::EVENT_LOAD_MODULES_POST, array($this, 'registerRouteModules'));
    }

    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();

        $eventManager->attach(MvcEvent::EVENT_RENDER, array($this, 'layoutHandler'));
        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'httpExceptionHandler'));

        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
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

    public function registerRouteModules(ModuleEvent $event)
    {
        foreach ($event->getTarget()->getLoadedModules() as $module) {
            if ($module instanceof RoutesInterface) {
                $this->routes[] = $module;
            }
            if ($module instanceof NavigationRoutesInterface) {
                $this->navigationRoutes[] = $module;
            }
        }
    }

    public function layoutHandler(MvcEvent $event)
    {
        $viewModel = $event->getViewModel();
        if (!($viewModel instanceof ViewModel)) {
            return;
        }

        $viewModel->setVariable('route', $event->getRouteMatch());
        $viewModel->setVariable('routePath', $this->getRoutePath($event->getRouteMatch()));

        $routes = array();
        foreach ($this->routes as $route) {
            $routes = array_merge($routes, $route->getLinkRoutes());
        }
        $viewModel->setVariable('routes', $routes);

        $navigationRoutes = array();
        foreach ($this->navigationRoutes as $navigationRoute) {
            $navigationRoutes = array_merge($navigationRoutes, $navigationRoute->getNavigationRoutes());
        }
        $viewModel->setVariable('navigationRoutes', $navigationRoutes);
    }

    protected function getRoutePath(RouteMatch $routeMatch = null)
    {
        $routePath = array();
        $fullPath = array();

        if ($routeMatch === null) {
            return $routePath;
        }

        foreach (explode('/', $routeMatch->getMatchedRouteName()) as $route) {
            $fullPath[] = $route;
            $routePath[implode('/', $fullPath)] = $route;
        }

        return $routePath;
    }

    public function httpExceptionHandler(MvcEvent $event)
    {
        $exception = $event->getParam('exception');
        if (!($exception instanceof HttpException)) {
            return;
        }

        $event->getResponse()->setStatusCode($exception->getHttpCode());
    }
}
