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