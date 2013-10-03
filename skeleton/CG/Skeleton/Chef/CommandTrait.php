<?php
namespace CG\Skeleton\Chef;

use CG\Skeleton\Arguments;
use CG\Skeleton\Config;

trait CommandTrait
{
    public function run(Arguments $arguments, Config $config)
    {
        $cwd = getcwd();
        chdir($config->getInfrastructurePath() . '/tools/chef');
        exec('git checkout ' . $config->getBranch() . ' 2>&1;');
        $this->runCommands($arguments, $config);
        chdir($cwd);
    }

    abstract protected function runCommands(Arguments $arguments, Config $config);
}