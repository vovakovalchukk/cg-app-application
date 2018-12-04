<?php

use CG\Profiling\Service as ProfilingService;

/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
define('PROJECT_ROOT', dirname(__DIR__));
define('DS', DIRECTORY_SEPARATOR);
chdir(PROJECT_ROOT);

require 'config/env.local.php';
require 'init_autoloader.php';

ProfilingService::startProfiling(
    'app',
    [],
    [
        ProfilingService::MODE_BASIC => 70,
        ProfilingService::MODE_PROFILE => 29,
        ProfilingService::MODE_TRACE => 1
    ]
);