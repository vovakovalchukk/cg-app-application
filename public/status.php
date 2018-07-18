<?php
use CG\Gearman\Client as CGGearmanClient;
use CG\Http\Guzzle\Http\FailoverClient as Guzzle;
use Predis\Client as Predis;
use Predis\ResponseErrorInterface as PredisError;
use Zend\Db\Sql\Sql;
use Zend\Di\Di;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;

try {
    $statusCode = 200;

    require_once __DIR__.'/../application/bootstrap.php';
    require 'init_autoloader.php';
    $appConfig = require 'config/application.config.php';

    $serviceManager = new ServiceManager(new ServiceManagerConfig($appConfig['service_manager'] ?? []));
    $serviceManager->setService('ApplicationConfig', $appConfig);
    $serviceManager->get('ModuleManager')->loadModules();

    if (ENVIRONMENT !== 'dev') {
        ob_start(function() { /* Ignore all output */ });
    }

    /** @var Di $di */
    $di = $serviceManager->get(Di::class);
    $getAliases = function($className) use($di): array {
        $className = (array) $className;
        return array_keys(array_filter(
            $di->instanceManager()->getAliases(),
            function(string $aliasClassName) use($className) {
                return in_array($aliasClassName, $className);
            }
        ));
    };

    $services = [
        'redis' => [
            'checks' => $getAliases(Predis::class),
            'checker' => function(string $connection) use($di): string {
                /** @var Predis $predis */
                $predis = $di->newInstance($connection);
                $ping = $predis->ping();
                if ($ping instanceof PredisError) {
                    throw new RuntimeException($ping->getMessage());
                }
                if ($ping !== true) {
                    throw new RuntimeException('Unknown connection error');
                }
                return 'passed';
            }
        ],
        'mysql' => [
            'checks' => $getAliases(Sql::class),
            'checker' => function(string $connection) use($di): string {
                /** @var Sql $sql */
                $sql = $di->newInstance($connection);
                /** @var mysqli $mysqli */
                $mysqli = $sql->getAdapter()->getDriver()->getConnection()->getResource();
                if ($mysqli->ping() !== true) {
                    throw new RuntimeException($mysqli->error, $mysqli->errno);
                }
                return 'passed';
            }
        ],
        'gearman' => [
            'checks' => $getAliases([GearmanClient::class, CGGearmanClient::class]),
            'checker' => function(string $connection) use($di): string {
                /** @var GearmanClient $gearmanClient */
                $gearmanClient = $di->newInstance($connection);
                if ($gearmanClient->ping('ping') !== true) {
                    throw new RuntimeException($gearmanClient->error(), $gearmanClient->getErrno());
                }
                return 'passed';
            }
        ],
        'apps' => [
            'checks' => $getAliases(Guzzle::class),
            'checker' => function(string $connection) use($di): string {
                if (in_array($connection, ['sso_guzzle'])) {
                    return 'skipped';
                }
                /** @var Guzzle $guzzle */
                $guzzle = $di->newInstance($connection);
                $request = $guzzle->head();
                $response = $guzzle->send($request);
                if ($response->getStatusCode() !== 200) {
                    throw new RuntimeException(sprintf('Recieved a %d response from %s', $response->getStatusCode(), $request->getUrl()));
                }
                return 'passed';
            }
        ],
    ];

    foreach ($services as $service => $config) {
        echo $service . PHP_EOL;
        foreach ($config['checks'] as $check) {
            echo sprintf('>>> %s: ', $check);
            try {
                echo $config['checker']($check);
            } catch (\Throwable $throwable) {
                $statusCode = 500;
                echo sprintf('%s %s', get_class($throwable), $throwable->getMessage());
            } finally {
                echo PHP_EOL;
            }
        }
        echo PHP_EOL;
    }
} catch (\Throwable $throwable) {
    $statusCode = 500;
    echo sprintf('%s %s', get_class($throwable), $throwable->getMessage()) . PHP_EOL;
} finally {
    header('Content-type: text/plain', true, $statusCode);
}