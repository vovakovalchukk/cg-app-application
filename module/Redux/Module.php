<?php
namespace Redux;

use Zend\Config\Factory as ConfigFactory;
use Zend\Loader\StandardAutoloader;

class Module
{
    const PUBLIC_FOLDER = '/cg-built/redux';
    const ROUTE = 'Redux Module';

    public function getConfig()
    {
        $configFiles = glob(__DIR__ . '/config/*.config.php');
        if (defined('PROJECT_ROOT') && is_file($configFile = PROJECT_ROOT  . '/config/module/' . __NAMESPACE__ . '.local.php')) {
            $configFiles[] = $configFile;
        }
        return ConfigFactory::fromFiles($configFiles);
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
