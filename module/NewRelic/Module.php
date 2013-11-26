<?php
namespace NewRelic;

use Zend\Mvc\MvcEvent;
use NewRelic\Api\Event\RegisterBrowserTimings;
use NewRelic\Api\Event\RegisterController;
use NewRelic\Api\Event\RegisterRoute;

class Module
{
    public function onBootstrap(MvcEvent $event)
    {
        if (!extension_loaded('newrelic')) {
            return;
        }

        $eventManager = $event->getApplication()->getEventManager();
        $serviceManager = $event->getApplication()->getServiceManager();

        $eventManager->attach(MvcEvent::EVENT_DISPATCH, $serviceManager->get(RegisterBrowserTimings::Class));
        $eventManager->attach(MvcEvent::EVENT_DISPATCH, $serviceManager->get(RegisterController::Class));
        $eventManager->attach(MvcEvent::EVENT_DISPATCH, $serviceManager->get(RegisterRoute::Class));
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
} 