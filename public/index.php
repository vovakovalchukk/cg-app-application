<?php
require_once __DIR__.'/../application/bootstrap.php';

// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

// Setup autoloading
require 'init_autoloader.php';

// Run the application!
$app = Zend\Mvc\Application::init(require 'config/application.config.php');
$app->getServiceManager()->get('Di')->get('CG\Log\FatalErrorHandler');
$app->run();
