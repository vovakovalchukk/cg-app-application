<?php
namespace Application;

use Zend\Mvc\MvcEvent;

class NewRelic
{
    public function __invoke(MvcEvent $event)
    {
        if (!extension_loaded('newrelic')) {
            return;
        }

        $this->registerRoute($event);
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

        $url = $event->getApplication()->getServiceManager()->get('viewhelpermanager')->get('url');
        newrelic_name_transaction(
            urldecode($url($routeMatch->getMatchedRouteName(), $parameters))
        );
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