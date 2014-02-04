<?php
namespace CG\Skeleton\DevelopmentEnvironment;

use CG\Skeleton\Arguments;
use CG\Skeleton\Config as SkeletonConfig;
use CG\Skeleton\DevelopmentEnvironment\Environment;

trait CommandTrait
{
    public function run(Arguments $arguments, SkeletonConfig $config, Environment $environment)
    {
        $cwd = getcwd();
        chdir($config->getInfrastructurePath() . '/tools/chef');
        exec('git checkout ' . $config->getBranch() . ' 2>&1;');
        $this->runCommands($arguments, $config, $environment);
        chdir($cwd);
    }

    abstract protected function runCommands(Arguments $arguments, SkeletonConfig $config, Environment $environment);
}