<?php
chdir(dirname(__DIR__));

use CG\Cilex\GenericCommand;
use CG\Cilex\ServiceProvider\Bootstrap;
use Cilex\Application as Cilex;
use Zend\Di\Di;
use Zend\Mvc\Application as Zend;

require_once 'application/bootstrap.php';
require_once 'init_autoloader.php';

$di = Zend::init(require 'config/console.config.php')->getServiceManager()->get(Di::class);
$commands = require_once 'config/console/commands.php';

/**
 * @var Cilex $app
 */
$app = $di->get(Cilex::class);
$app->register($di->get(Bootstrap::class));
foreach ($commands as $commandName => $command) {
    $app->command($di->newInstance(GenericCommand::class, ['commandName' => $commandName, 'command' => $command]));
}
$app->run();
