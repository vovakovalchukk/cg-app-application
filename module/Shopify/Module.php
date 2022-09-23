<?php
namespace Shopify;

use Zend\Config\Factory as ConfigFactory;
use Zend\Loader\StandardAutoloader;

class Module
{
    public const PUBLIC_FOLDER = '/cg-built/shopify';

    public const ROUTE = 'Shopify';

    public function getConfig()
    {
        return ConfigFactory::fromFiles(
            glob(__DIR__ . '/config/*.config.php')
        );
    }

    public function getAutoloaderConfig()
    {
        return array(
            StandardAutoloader::class => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
}
