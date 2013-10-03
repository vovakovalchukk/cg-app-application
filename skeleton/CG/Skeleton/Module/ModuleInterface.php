<?php
namespace CG\Skeleton\Module;

use CG\Skeleton\CommandInterface;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config as SkeletonConfig;

interface ModuleInterface extends CommandInterface
{
    public function getModuleName();
    public function run(Arguments $arguments, SkeletonConfig $config, BaseConfig $moduleConfig = null);
}