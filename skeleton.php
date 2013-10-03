#!/usr/bin/php
<?php
declare(ticks = 1);
require 'skeleton/bootstrap.php';
$application = $di->get('CG\Skeleton\Application');
pcntl_signal(SIGTERM, array($application, 'shutdown'));
pcntl_signal(SIGINT, array($application, 'shutdown'));
$application->run();