<?php
namespace CG\Skeleton\Module;

use CG\Skeleton\Arguments;
use CG\Skeleton\Config as SkeletonConfig;
use CG\Skeleton\Module\BaseConfig;

interface ConfigureInterface extends ModuleInterface, ApplyConfigurationInterface
{
    public function configure(Arguments $arguments, SkeletonConfig $config, BaseConfig $moduleConfig);
}