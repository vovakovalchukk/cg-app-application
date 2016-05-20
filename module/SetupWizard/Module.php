<?php
namespace SetupWizard;

use Zend\ModuleManager\Feature\DependencyIndicatorInterface;
use Zend\Config\Factory as ConfigFactory;
use Zend\Mvc\MvcEvent;

class Module implements DependencyIndicatorInterface
{
    const PUBLIC_FOLDER = '/cg-built/setup-wizard/';
    const ROUTE = 'SetupWizard';

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
