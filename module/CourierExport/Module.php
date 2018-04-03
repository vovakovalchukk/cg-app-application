<?php
namespace CourierExport;

use Zend\Config\Factory as ConfigFactory;
use Zend\Loader\StandardAutoloader;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{
    const PUBLIC_FOLDER = '/cg-built/courier-export';

    public function getConfig()
    {
        return ConfigFactory::fromFiles(glob(__DIR__ . '/config/*.config.php'));
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
}
