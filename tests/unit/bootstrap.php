<?php
define('PROJECT_ROOT', dirname(dirname(__DIR__)));
define('TEST_ROOT', __DIR__);

echo PROJECT_ROOT.'/vendor/autoload.php' . "\n";
echo PROJECT_ROOT.'/vendor/channelgrabber/stdlib/tests/external-bootstrap.php' . "\n";

require_once PROJECT_ROOT.'/vendor/autoload.php';
require_once PROJECT_ROOT.'/vendor/channelgrabber/stdlib/tests/external-bootstrap.php';

require 'init_autoloader.php';