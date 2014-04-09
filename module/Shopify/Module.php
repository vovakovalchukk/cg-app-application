<?php
namespace CG_Amazon;

use Zend\Config\Factory as ConfigFactory;
use Zend\Loader\StandardAutoloader;
use Zend\ModuleManager\Feature\DependencyIndicatorInterface;

class Module implements DependencyIndicatorInterface
{
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