<?php
namespace SetupWizard;

use CG\Http\StatusCode;
use CG\PasswordResetToken\Email\Controller as LoginPasswordController;
use CG_SSO\Module as CG_SSO;
use Zend\Config\Factory as ConfigFactory;
use Zend\Di\Di;
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
        // The ordering of these is important - must save the current status before interrogating it
        $eventManager->attach(MvcEvent::EVENT_DISPATCH, [$this, 'processStepStatus']);
        $eventManager->attach(MvcEvent::EVENT_DISPATCH, [$this, 'constrainToWizard']);
    }

    public function appendStylesheet(MvcEvent $e)
    {
        $serviceManager = $e->getApplication()->getServiceManager();
        $renderer = $serviceManager->get(PhpRenderer::class);
        $basePath = $serviceManager->get('viewhelpermanager')->get('basePath');
        $renderer->headLink()->appendStylesheet($basePath() . static::PUBLIC_FOLDER . 'css/default.css');
    }

    public function processStepStatus(MvcEvent $e)
    {
        $currentStep = null;
        $route = $e->getRouteMatch()->getMatchedRouteName();
        if (preg_match('/^' . static::ROUTE . '\/([^\/]+)(\/[^\/]+)?/', $route, $matches)) {
            // Don't process sub steps, only main steps
            if (!isset($matches[2]) || !$matches[2]) {
                $currentStep = $matches[1];
            }
        }
        $request = $e->getRequest();
        $previousStep = $request->getQuery('prev');
        $previousStepStatus = $request->getQuery('status');
        if (!$currentStep && (!$previousStep || !$previousStepStatus)) {
            return;
        }
        $di = $e->getApplication()->getServiceManager()->get(Di::class);
        /** @var StepStatusService $service */
        $service = $di->get(StepStatusService::class);
        $service->processStepStatus($previousStep, $previousStepStatus, $currentStep);
    }

    public function constrainToWizard(MvcEvent $e)
    {
        $route = $e->getRouteMatch()->getMatchedRouteName();
        if ($this->isLoginRoute($route) || $this->isSsoRoute($route) || $this->isSetupWizardRoute($route)) {
            return;
        }
        $di = $e->getApplication()->getServiceManager()->get(Di::class);
        /** @var StepStatusService $service */
        $service = $di->get(StepStatusService::class);
        $redirectRoute = $service->getRedirectRouteIfIncomplete($route);
        if (!$redirectRoute) {
            return;
        }
        return $this->redirectToRoute($redirectRoute, $e);
    }

    protected function isLoginRoute($route)
    {
        return preg_match('/^(cg_login|' . preg_quote(LoginPasswordController::ROUTE, '/') . ')/', $route);
    }

    protected function isSsoRoute($route)
    {
        $routes = [
            preg_quote(CG_SSO::ROUTE_RETURN, '/'),
            preg_quote(CG_SSO::ROUTE_LOGOUT, '/'),
        ];
        return preg_match('/^(' . implode('|', $routes) . ')/', $route);
    }

    protected function isSetupWizardRoute($route)
    {
        return preg_match('/^' . preg_quote(static::ROUTE, '/') . '/', $route);
    }

    protected function redirectToRoute($route, MvcEvent $e)
    {
        $url = $e->getRouter()->assemble([], ['name' => $route]);

        $response = $e->getResponse();
        $e->stopPropagation();
        $response->getHeaders()->addHeaderLine('Location', $url);
        $response->setStatusCode(StatusCode::TEMPORARY_REDIRECT);
        $response->sendHeaders();
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
            'CG_Login',
            'CG_Register',
            'Settings',
        ];
    }
}
