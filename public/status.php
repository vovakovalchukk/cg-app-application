<?php
use Predis\Client as Predis;
use Predis\ResponseErrorInterface as PredisError;
use Zend\Db\Sql\Sql;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;
use Guzzle\Http\Client as Guzzle;

try {
    $statusCode = 200;

    require_once __DIR__.'/../application/bootstrap.php';
    require 'init_autoloader.php';
    $appConfig = require 'config/application.config.php';

    $serviceManager = new ServiceManager(new ServiceManagerConfig($appConfig['service_manager'] ?? []));
    $serviceManager->setService('ApplicationConfig', $appConfig);
    $serviceManager->get('ModuleManager')->loadModules();

    $services = [
        'redis' => [
            'checks' => ['unreliable_redis', 'reliable_redis', 'audit_redis', 'logging_redis'],
            'checker' => function(string $connection) use($serviceManager) {
                /** @var Predis $predis */
                $predis = $serviceManager->get($connection);
                $ping = $predis->ping();
                if ($ping instanceof PredisError) {
                    throw new RuntimeException($ping->getMessage());
                }
                if ($ping !== true) {
                    throw new RuntimeException('Unknown connection error');
                }
            }
        ],
        'mysql' => [
            'checks' => ['ReadSql', 'appReadSql', 'amazonReadSql'],
            'checker' => function(string $connection) use($serviceManager) {
                /** @var Sql $sql */
                $sql = $serviceManager->get($connection);
                /** @var mysqli $mysqli */
                $mysqli = $sql->getAdapter()->getDriver()->getConnection()->getResource();
                if ($mysqli->ping() !== true) {
                    throw new RuntimeException($mysqli->error, $mysqli->errno);
                }
            }
        ],
        'gearman' => [
            'checks' => ['defaultGearmanClient'],
            'checker' => function(string $connection) use($serviceManager) {
                /** @var GearmanClient $gearmanClient */
                $gearmanClient = $serviceManager->get($connection);
                if ($gearmanClient->ping('ping') !== true) {
                    throw new RuntimeException($gearmanClient->error(), $gearmanClient->getErrno());
                }
            }
        ],
        'apps' => [
            'checks' => ['billing_guzzle', 'cg_app_guzzle', 'account_guzzle', 'directory_guzzle', 'image_guzzle', 'communication_guzzle', 'feature-flags_guzzle'],
            'checker' => function(string $connection) use($serviceManager) {
                /** @var Guzzle $guzzle */
                $guzzle = $serviceManager->get($connection);
                $request = $guzzle->head();
                $response = $guzzle->send($request);
                if ($response->getStatusCode() !== 200) {
                    throw new RuntimeException(sprintf('Recieved a %d response from %s', $response->getStatusCode(), $request->getUrl()));
                }
            }
        ],
    ];

    foreach ($services as $service => $config) {
        echo $service . PHP_EOL;
        foreach ($config['checks'] as $check) {
            echo sprintf('>>> %s: ', $check);
            try {
                $config['checker']($check);
                echo 'passed';
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