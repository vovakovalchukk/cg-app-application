<?php
namespace NewRelic;

use Zend\Mvc\MvcEvent;
use Exception;

class NewRelic
{
    public function __invoke(MvcEvent $event)
    {
        if (!extension_loaded('newrelic')) {
            return;
        }

        $this->registerRoute($event);
        $this->registerController($event);
        $this->registerBrowserTimings($event);
    }

    protected function registerRoute(MvcEvent $event)
    {
        $routeMatch = $event->getRouteMatch();
        if (!$routeMatch) {
            return;
        }

        $keys = array_keys($routeMatch->getParams());

        $parameters = array_combine(
            $keys,
            array_map(
                function($key) {
                    return ':' . $key;
                },
                $keys
            )
        );

        try {
            $url = $event->getApplication()->getServiceManager()->get('viewhelpermanager')->get('url');
            newrelic_name_transaction(
                urldecode($url($routeMatch->getMatchedRouteName(), $parameters))
            );
        } catch (Exception $exception) {
            // Ignore exception - Can't log url
        }
    }

    protected function registerController(MvcEvent $event)
    {
        $routeMatch = $event->getRouteMatch();
        if (!$routeMatch) {
            return;
        }

        $controller = array(
            'controller' => $routeMatch->getParam('controller'),
            'action' => $routeMatch->getParam('action')
        );

        newrelic_add_custom_tracer(implode('::', $controller));
        foreach ($controller as $key => $value) {
            newrelic_add_custom_parameter($key, $value);
        }
    }

    protected function registerBrowserTimings(MvcEvent $event)
    {
        $viewHelper = $event->getApplication()->getServiceManager()->get('viewhelpermanager');

        $viewHelper->get('headscript')->prependScript(
            newrelic_get_browser_timing_header(false)
        );

        $viewHelper->get('inlinescript')->appendScript(
            newrelic_get_browser_timing_footer(false)
        );
    }
} 