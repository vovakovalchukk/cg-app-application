<?php
namespace Ekm;

use Zend\Config\Factory as ConfigFactory;
use Zend\Loader\StandardAutoloader;

class Module
{
    const PUBLIC_FOLDER = '/cg-built/ekm/';

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
                    __NAMESPACE__ => __DIR__ . '/src/',
                ],
            ],
        ];
    }

    public function getModuleDependencies()
    {
        return ['CG_Register', 'CG_Login', 'CG_UI', 'SetupWizard', 'Orders'];
    }
}
