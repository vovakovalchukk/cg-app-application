<?php
namespace NewRelic\Api\Event;

use Zend\Mvc\MvcEvent;

class RegisterController
{
    public function __invoke(MvcEvent $event)
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
} 