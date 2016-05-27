<?php
namespace SetupWizard;

use SetupWizard\StepStatusService;
use Zend\Di\Di;
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
        $eventManager->attach(MvcEvent::EVENT_DISPATCH, [$this, 'checkForStepStatus']);
    }

    public function appendStylesheet(MvcEvent $e)
    {
        $serviceManager = $e->getApplication()->getServiceManager();
        $renderer = $serviceManager->get(PhpRenderer::class);
        $basePath = $serviceManager->get('viewhelpermanager')->get('basePath');
        $renderer->headLink()->appendStylesheet($basePath() . static::PUBLIC_FOLDER . 'css/default.css');
    }

    public function checkForStepStatus(MvcEvent $e)
    {
        $request = $e->getRequest();
        $previousStep = $request->getQuery('prev');
        $previousStepStatus = $request->getQuery('status');
        if (!$previousStep || !$previousStepStatus) {
            return;
        }
        $di = $e->getApplication()->getServiceManager()->get(Di::class);
        $service = $di->get(StepStatusService::class);
        $service($previousStep, $previousStepStatus);
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
