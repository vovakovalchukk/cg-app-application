<?php
chdir(dirname(__DIR__));

define('PROJECT_NAME', basename(dirname(__DIR__)));
define('SKELETON_CONFIG', __DIR__ . '/config.xml');

if (!is_file('composer.phar')) {
    passthru('curl -sS https://getcomposer.org/installer | php');
}

if (!is_file('vendor/autoload.php')) {
    passthru('php composer.phar install');
}

$autoloader = require 'vendor/autoload.php';
$autoloader->set('CG\Skeleton', __DIR__);

$config = array();
if (is_file(SKELETON_CONFIG)) {
    $config = Zend\Config\Factory::fromFile(SKELETON_CONFIG) ?: array();
}

$di = new Zend\Di\Di(null, null, new Zend\Di\Config(require_once 'skeleton/config/di.php'));
$di->instanceManager()->addSharedInstance(new CG\Skeleton\Config($config, true), 'CG\Skeleton\Config');