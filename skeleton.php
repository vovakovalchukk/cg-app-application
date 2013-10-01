#!/usr/bin/php
<?php
require 'skeleton/bootstrap.php';

use Zend\Config\Factory;
use Zend\Config\Config;
use CG\Skeleton\Setup;
use CG\Skeleton\Arguments;

$commands = new SplObjectStorage();

$config = array();
if (is_file(SKELETON_CONFIG)) {
    $config = Factory::fromFile(SKELETON_CONFIG) ?: array();
}

$setup = new Setup(
    $commands,
    new Arguments(),
    new Config($config, true)
);

$setup->run();

Factory::toFile(SKELETON_CONFIG, $setup->getConfig());