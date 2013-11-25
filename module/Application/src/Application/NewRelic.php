<?php
namespace Application;

use Zend\View\HelperPluginManager;
use Zend\Mvc\MvcEvent;

class NewRelic
{
    protected $url;
    protected $headscript;
    protected $inlinescript;

    public function __construct(HelperPluginManager $viewHelper)
    {
        $this->url = $viewHelper->get('url');
        $this->headscript = $viewHelper->get('headscript');
        $this->inlinescript = $viewHelper->get('inlinescript');
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

        newrelic_name_transaction(
            urldecode($this->url($routeMatch->getMatchedRouteName(), $parameters))
        );
    }

    protected function registerBrowserTimings(MvcEvent $event)
    {
        $this->headscript->prependScript(
            newrelic_get_browser_timing_header(false)
        );

        $this->inlinescript->appendScript(
            newrelic_get_browser_timing_footer(false)
        );
    }
} 