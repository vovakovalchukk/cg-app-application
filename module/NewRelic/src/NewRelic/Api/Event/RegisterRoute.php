<?php
namespace NewRelic\Api\Event;

use Zend\Mvc\MvcEvent;
use Exception;

class RegisterRoute
{
    public function __invoke(MvcEvent $event)
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
} 