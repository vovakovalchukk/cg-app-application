<?php
use CG\CGLib\Status;
use CG\Log\Logger;
use CG\Stdlib\Log\LogTrait;
use Zend\Mvc\Router\Http\TreeRouteStack as Router;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;

try {
    $statusCode = 200;

    require_once __DIR__.'/../application/bootstrap.php';
    require 'init_autoloader.php';
    $appConfig = require 'config/application.config.php';

    $serviceManager = new ServiceManager(new ServiceManagerConfig($appConfig['service_manager'] ?? []));
    $serviceManager->setService('ApplicationConfig', $appConfig);
    $serviceManager->addDelegator('Router', function() { return new Router(); });
    $serviceManager->get('ModuleManager')->setModules(['CG_Log'])->loadModules();
    $serviceManager->get('Application')->bootstrap();

    if (ENVIRONMENT !== 'dev') {
        ob_start(function() { /* Ignore all output */ });
    }

    /** @var Status $statusChecker */
    $statusChecker = $serviceManager->get(Status::class);
    $detailStatus = $statusChecker->getStatus($status);

    if (!$status) {
        $statusCode = 500;
    }

    foreach ($detailStatus as $service => $serviceStatus) {
        echo $service . PHP_EOL;
        foreach ($serviceStatus as $check => $checkStatus) {
            echo sprintf('>>> %s: %s', $check, $checkStatus) . PHP_EOL;
        }
        echo PHP_EOL;
    }
} catch (\Throwable $throwable) {
    $statusCode = 500;
    echo get_class($throwable) . PHP_EOL . $throwable->getMessage() . PHP_EOL . $throwable->getTraceAsString();
    if (!isset($serviceManager)) {
        return;
    }
    try {
        $logger = new class($serviceManager->get(Logger::class))
        {
            use LogTrait;

            public function __construct(Logger $logger)
            {
                $this->logger = $logger;
            }
        };
        $logger->logEmergencyException($throwable, 'Unknown status check failure', [], Status::LOG_CODE);
    } catch (\Throwable $throwable) {
        // Ignore log failures
    }
} finally {
    header('Content-type: text/plain', true, $statusCode);
}