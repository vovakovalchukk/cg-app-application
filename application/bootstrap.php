<?php
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
define('PROJECT_ROOT', dirname(__DIR__));
define('DS', DIRECTORY_SEPARATOR);
chdir(PROJECT_ROOT);

require 'config/env.local.php';
require 'init_autoloader.php';

// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}