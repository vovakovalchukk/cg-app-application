<?php
namespace CG\Skeleton\Module;

use CG\Skeleton\Arguments;
use CG\Skeleton\Config as SkeletonConfig;
use CG\Skeleton\Module\BaseConfig;

interface EnableInterface extends ModuleInterface
{
    public function enable(Arguments $arguments, SkeletonConfig $config, BaseConfig $moduleConfig);
}