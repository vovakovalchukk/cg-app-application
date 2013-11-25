<?php
namespace Application;

use Zend\View\HelperPluginManager;
use Zend\Mvc\MvcEvent;

class NewRelic
{
    protected $viewHelper;

    public function __construct(HelperPluginManager $viewHelper)
    {
        $this->setViewHelper($viewHelper);
    }

    public function setViewHelper(HelperPluginManager $viewHelper)
    {
        $this->viewHelper = $viewHelper;
        return $this;
    }

    public function getViewHelper()
    {
        return $this->viewHelper;
    }

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

        $url = $this->getViewHelper()->get('url');
        newrelic_name_transaction(
            urldecode($url($routeMatch->getMatchedRouteName(), $parameters))
        );
    }

    protected function registerBrowserTimings(MvcEvent $event)
    {
        $this->getViewHelper()->get('headscript')->prependScript(
            newrelic_get_browser_timing_header(false)
        );

        $this->getViewHelper()->get('inlinescript')->appendScript(
            newrelic_get_browser_timing_footer(false)
        );
    }
} 