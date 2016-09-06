<?php
namespace BigCommerce;

use Zend\Config\Factory as ConfigFactory;
use Zend\Loader\StandardAutoloader;

class Module
{
    const PUBLIC_FOLDER = '/cg-built/bigcommerce';

    const ROUTE = 'BigCommerce';

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
}
