<?php
use CG\CGLib\Status;
use CG\CGLib\Status\Cache as StatusCache;
use CG\CGLib\Status\Zf2Factory;
use CG\Log\Logger;
use CG\Stdlib\Log\LogTrait;

try {
    require_once __DIR__ . '/../application/bootstrap.php';

    if (!defined('ENVIRONMENT') || ENVIRONMENT !== 'dev') {
        ob_start(function() { /* Ignore all output */ });
    }

    if (extension_loaded('newrelic')) {
        newrelic_name_transaction('status');
    }

    $statusCode = 200;
    $cachedStatus = StatusCache::getCachedStatus($factory = new Zf2Factory());

    if (!$cachedStatus->getStatus()) {
        $statusCode = 500;
    }

    foreach ($cachedStatus->getDetails() as $service => $serviceStatus) {
        echo $service . PHP_EOL;
        foreach ($serviceStatus as $check => $checkStatus) {
            echo sprintf('>>> %s: %s', $check, $checkStatus) . PHP_EOL;
        }
        echo PHP_EOL;
    }
} catch (\Throwable $throwable) {
    $statusCode = 500;
    echo get_class($throwable) . PHP_EOL . $throwable->getMessage() . PHP_EOL . $throwable->getTraceAsString();
    if (!isset($factory)) {
        return;
    }
    try {
        $logger = new class($factory->getServiceManager()->get(Logger::class))
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
    header('Content-type: text/plain', true, $statusCode ?? 500);
}