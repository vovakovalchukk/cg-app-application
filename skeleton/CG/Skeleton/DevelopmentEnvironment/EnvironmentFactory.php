<?php
namespace CG\Skeleton\DevelopmentEnvironment;

use CG\Skeleton\Config as SkeletonConfig;
use CG\Skeleton\Vagrant\NodeData;

class EnvironmentFactory
{
    const ENVIRONMENT_NAMESPACE = 'CG\Skeleton\DevelopmentEnvironment\Environment\\';

    protected static $environments = array('Local', 'Dual');

    public static function build(SkeletonConfig $config, NodeData $nodeData)
    {
        $environmentClass = static::ENVIRONMENT_NAMESPACE . $config->getEnvironment();
        return new $environmentClass($config, $nodeData);
    }

    public static function getEnvironments()
    {
        return self::$environments;
    }
}