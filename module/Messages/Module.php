<?php
namespace Messages;

use Zend\ModuleManager\Feature\DependencyIndicatorInterface;
use Zend\Config\Factory as ConfigFactory;
use Zend\Mvc\MvcEvent;

class Module implements DependencyIndicatorInterface
{
    //const PUBLIC_FOLDER = '/cg-built/messages/';
    const PUBLIC_FOLDER = '/channelgrabber/messages/'; // TODO: replace this with the above!
    const ROUTE = 'Messages';

    public function onBootstrap(MvcEvent $event)
    {
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
            'CG_UI',
        ];
    }
}
