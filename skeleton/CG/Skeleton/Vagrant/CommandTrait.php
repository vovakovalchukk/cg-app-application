<?php
namespace CG\Skeleton\Vagrant;

use CG\Skeleton\Arguments;
use CG\Skeleton\Config;

trait CommandTrait
{
    public function run(Arguments $arguments, Config $config)
    {
        $cwd = getcwd();
        chdir($config->getInfrastructurePath() . '/tools/vagrant');
        exec('git checkout ' . $config->getBranch() . ' > /dev/null;');
        $this->runCommands($arguments, $config);
        chdir($cwd);
    }

    abstract protected function runCommands(Arguments $arguments, Config $config);
}