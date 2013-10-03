<?php
namespace CG\Skeleton\Module;

use CG\Skeleton\Arguments;
use CG\Skeleton\Config as SkeletonConfig;
use CG\Skeleton\Module\BaseConfig;

interface DisableInterface extends ModuleInterface
{
    public function disable(Arguments $arguments, SkeletonConfig $config, BaseConfig $moduleConfig);
}