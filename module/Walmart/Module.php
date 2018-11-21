<?php
namespace Walmart;

use Zend\Config\Factory as ConfigFactory;
use Zend\Loader\StandardAutoloader;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\DependencyIndicatorInterface;

class Module implements ConfigProviderInterface, AutoloaderProviderInterface, DependencyIndicatorInterface
{
    const ROUTE = 'Walmart';
    const PUBLIC_FOLDER = '/cg-built/walmart';

    public function getConfig()
    {
        return ConfigFactory::fromFiles(
            glob(__DIR__ . '/config/*.config.php')
        );
    }

    public function getAutoloaderConfig()
    {
        return [
            StandardAutoloader::class => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ],
            ],
        ];
    }

    public function getModuleDependencies()
    {
        return ['CG_UI', 'CG_Channel', 'SetupWizard', 'Settings'];
    }
}