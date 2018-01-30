<?php
chdir(dirname(__DIR__));

use CG\Cilex\GenericCommand;
use CG\Cilex\ServiceProvider\Bootstrap;
use CG_Sessions\GCCommand as SessionGarbageCollection;
use Cilex\Application as Cilex;
use Zend\Di\Config as DiConfig;
use Zend\Di\Di;
use Zend\Mvc\Application as Zend;

require_once 'application/bootstrap.php';
require_once 'init_autoloader.php';

$serviceManager = Zend::init(require 'config/console.config.php')->getServiceManager();
$di = $serviceManager->get(Di::class);
$commands = require_once 'config/console/commands.php';

foreach (['CG_Sessions'] as $modules) {
    (new DiConfig($serviceManager->get('ModuleManager')->loadModule($modules)->getConfig()['di'] ?? []))->configure($di);
}

/** @var Cilex $app */
$app = $di->get(Cilex::class);
$app->register($di->get(Bootstrap::class));
$app->command($di->get(SessionGarbageCollection::class));
foreach ($commands as $commandName => $command) {
    $app->command($di->newInstance(GenericCommand::class, ['commandName' => $commandName, 'command' => $command]));
}
$app->run();
