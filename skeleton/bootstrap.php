<?php
chdir(dirname(__DIR__));

define('PROJECT_NAME', basename(dirname(__DIR__)));
define('SKELETON_CONFIG', __DIR__ . '/config.xml');

exec('command -v composer >/dev/null 2>&1', $output, $composer_return);
if ($composer_return != 0) {
    echo "[Error]\t Composer is not installed globally. "
    . "Please see http://getcomposer.org/doc/00-intro.md#globally for more details.\n";
    exit(1);
}

if (!is_file('vendor/autoload.php')) {
    passthru('composer install');
}

$autoloader = require 'vendor/autoload.php';
$autoloader->set('CG\Skeleton', __DIR__);

$config = array();
if (is_file(SKELETON_CONFIG)) {
    $config = Zend\Config\Factory::fromFile(SKELETON_CONFIG) ?: array();
}

$di = new Zend\Di\Di(null, null, new Zend\Di\Config(require_once 'skeleton/config/di.php'));
$di->instanceManager()->addSharedInstance(new CG\Skeleton\Config($config, true), 'CG\Skeleton\Config');