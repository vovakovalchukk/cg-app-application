<?php
require_once TEST_PROJECT_ROOT.'/application/bootstrap.php';
require_once TEST_PROJECT_ROOT.'/init_autoloader.php';

use Zend\Di\Di;
use Zend\Mvc\Application as Zend;

$di = Zend::init(require 'config/console.config.php')->getServiceManager()->get(Di::class);
