<?php
namespace CG\Skeleton\Module;

use CG\Skeleton\CommandInterface;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config as SkeletonConfig;
use CG\Skeleton\DevelopmentEnvironment\Environment;

interface ModuleInterface extends CommandInterface
{
    public function getModuleName();
    public function run(Arguments $arguments, SkeletonConfig $config, Environment $environment, BaseConfig $moduleConfig = null);
}