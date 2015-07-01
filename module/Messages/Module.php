<?php
namespace Messages;

use Messages\Thread\Service as ThreadService;
use Zend\ModuleManager\Feature\DependencyIndicatorInterface;
use Zend\Config\Factory as ConfigFactory;
use Zend\Di\Di;
use Zend\Mvc\MvcEvent;

class Module implements DependencyIndicatorInterface
{
    const PUBLIC_FOLDER = '/cg-built/messages/';
    const ROUTE = 'Messages';

    public function onBootstrap(MvcEvent $event)
    {
        $serviceManager = $event->getApplication()->getServiceManager();
        $config = $serviceManager->get('Config');
        $di = $serviceManager->get(Di::class);
        $amendedConfig = $this->amendNavigationConfig($config, $di);
        $serviceManager->setService('Config', $amendedConfig);
    }

    protected function amendNavigationConfig(array $config, Di $di)
    {
        $service = $di->get(ThreadService::class);
        if ($service->hasNew()) {
            // TODO: get proper sprite
            $config['navigation']['application-navigation']['messages']['sprite'] = 'sprite-cog-18-white';
        }
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
