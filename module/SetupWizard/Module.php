<?php
namespace SetupWizard;

use Zend\Config\Factory as ConfigFactory;
use Zend\ModuleManager\Feature\DependencyIndicatorInterface;
use Zend\Mvc\MvcEvent;
use Zend\View\Renderer\PhpRenderer;

class Module implements DependencyIndicatorInterface
{
    const PUBLIC_FOLDER = '/cg-built/setup-wizard/';
    const ROUTE = 'SetupWizard';

    public function onBootstrap(MvcEvent $event)
    {
        $eventManager = $event->getApplication()->getEventManager();
        $eventManager->attach(MvcEvent::EVENT_RENDER, [$this, 'appendStylesheet']);
    }

    public function appendStylesheet(MvcEvent $e)
    {
        $serviceManager = $e->getApplication()->getServiceManager();
        $renderer = $serviceManager->get(PhpRenderer::class);
        $basePath = $serviceManager->get('viewhelpermanager')->get('basePath');
        $renderer->headLink()->appendStylesheet($basePath() . static::PUBLIC_FOLDER . 'css/default.css');
    }

    public function getConfig()
    {
        $configFiles = array_merge(glob(__DIR__ . '/config/*.config.php'), glob(__DIR__ . '/config/steps/*.config.php'));
        return ConfigFactory::fromFiles($configFiles);
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
