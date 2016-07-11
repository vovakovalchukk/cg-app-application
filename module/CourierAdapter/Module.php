<?php
namespace CourierAdapter;

use Zend\Config\Factory as ConfigFactory;
use Zend\Loader\StandardAutoloader;

class Module
{
    const ROUTE = 'Courier Adapter Module';
//    const PUBLIC_FOLDER = '/cg-built/courier-adapter';
    const PUBLIC_FOLDER = '/channelgrabber/courier-adapter';

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