<?php
namespace CG\Skeleton\Module;

use CG\Skeleton\StartupCommandInterface;
use CG\Skeleton\Console\Startup;
use SplObjectStorage;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config as SkeletonConfig;
use CG\Skeleton\Module\ApplyConfigurationInterface;
use CG\Skeleton\Module\Config as ModuleConfig;

class StartupCommand implements StartupCommandInterface
{
    protected $console;
    protected $modules;

    public function __construct(Startup $console)
    {
        $this->setConsole($console);
        $this->modules = new SplObjectStorage();
    }

    public function setConsole(Startup $console)
    {
        $this->console = $console;
        return $this;
    }

    public function getConsole()
    {
        return $this->console;
    }

    public function addModule(ApplyConfigurationInterface $module)
    {
        $this->modules->attach($module);
        return $this;
    }

    public function getModules()
    {
        return $this->modules;
    }

    public function run(Arguments $arguments, SkeletonConfig $config)
    {
        $moduleConfig = $config->get('Module', new ModuleConfig(array(), true));
        foreach ($this->getModules() as $module) {
            $module->applyConfiguration($arguments, $config, $moduleConfig->getModule($module->getModuleName()));
            $this->getConsole()->writeStatus('Applied Configuration for ' . $module->getName());
        }
    }
}