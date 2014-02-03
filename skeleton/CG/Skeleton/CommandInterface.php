<?php
namespace CG\Skeleton;

use CG\Skeleton\DevelopmentEnvironment\Environment;

interface CommandInterface
{
    public function getName();
    public function run(Arguments $arguments, Config $config, Environment $environment);
}