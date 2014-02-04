<?php
namespace CG\Skeleton;

use CG\Skeleton\DevelopmentEnvironment\Environment;

interface StartupCommandInterface
{
    public function run(Arguments $arguments, Config $config, Environment $environment);
}