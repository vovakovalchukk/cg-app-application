<?php
namespace Products;

use Zend\ModuleManager\Feature\DependencyIndicatorInterface;
use Zend\Config\Factory as ConfigFactory;
use Zend\Mvc\MvcEvent;

class Module implements DependencyIndicatorInterface
{
    const ROUTE = 'Products';

    public function onBootstrap(MvcEvent $event)
    {
        $eventManager = $event->getApplication()->getEventManager();
        $eventManager->attach(MvcEvent::EVENT_RENDER, array($this, 'layoutHandler'));
    }

    public function getConfig()
    {
        return ConfigFactory::fromFiles(
            glob(__DIR__ . '/config/*.config.php')
        );
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

    public function getModuleDependencies()
    {
        return [
            'CG_UI'
        ];
    }
}
