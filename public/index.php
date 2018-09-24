<?php
require_once __DIR__.'/../application/bootstrap.php';

// Run the application!
$app = Zend\Mvc\Application::init(require 'config/application.config.php');
$app->run();
fastcgi_finish_request();
gc_collect_cycles();
gc_enable();