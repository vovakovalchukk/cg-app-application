<?php
namespace NewRelic\Api\Event;

use Zend\Mvc\MvcEvent;

class RegisterBrowserTimings
{
    public function __invoke(MvcEvent $event)
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