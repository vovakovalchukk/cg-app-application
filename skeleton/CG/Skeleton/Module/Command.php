<?php
namespace CG\Skeleton\Module;

use CG\Skeleton\CommandInterface;
use CG\Skeleton\Console;
use CG\Skeleton\DevelopmentEnvironment\Environment;
use SplObjectStorage;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config as SkeletonConfig;
use CG\Skeleton\Console\Lists\Modules;

class Command implements CommandInterface
{
    protected $console;
    protected $modules;
    protected $defaults;

    public function __construct(Console $console)
    {
        $this->setConsole($console);
        $this->modules = new SplObjectStorage();
        $this->defaults = array();
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

    public function addModule(ModuleInterface $module)
    {
        $this->modules->attach($module);
        return $this;
    }

    public function getModules()
    {
        return $this->modules;
    }

    public function getName()
    {
        return 'Configure Modules';
    }

    public function run(Arguments $arguments, SkeletonConfig $config, Environment $environment)
    {
        $moduleConfig = $config->get('Module', new Config($this->defaults, true));
        while ($this->moduleList($arguments, $config, $moduleConfig, $environment));
        $config->offsetSet('Module', $moduleConfig);
    }

    public function moduleList(Arguments $arguments, SkeletonConfig $config, Config $moduleConfig, Environment $environment)
    {
        $moduleList = new Modules($this->getConsole(), $this->getModules(), $moduleConfig, $environment);
        return $moduleList->askAndRun($arguments, $config, $environment);
    }
}