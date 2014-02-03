<?php
namespace CG\Skeleton\Vagrant;

use CG\Skeleton\Arguments;
use CG\Skeleton\Config as SkeletonConfig;
use CG\Skeleton\DevelopmentEnvironment\Environment;
use CG\Skeleton\Console;

trait CommandTrait
{
    protected $console;

    public function __construct(Console $console)
    {
        $this->setConsole($console);
    }

    public function setConsole(Console $console)
    {
        $this->console = $console;
        return $this;
    }

    public function getConsole()
    {
        return $this->console;
    }

    public function run(Arguments $arguments, SkeletonConfig $config, Environment $environment)
    {
        $cwd = getcwd();
        chdir($config->getInfrastructurePath() . '/tools/vagrant');
        exec('git checkout ' . $config->getBranch() . ' 2>&1;');
        $this->runCommands($arguments, $config, $environment);
        chdir($cwd);
    }

    abstract protected function runCommands(Arguments $arguments, SkeletonConfig $config, Environment $environment);
}