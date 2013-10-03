<?php
namespace CG\Skeleton\Module;

use CG\Skeleton\Module\ModuleInterface;
use CG\Skeleton\Console;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config as SkeletonConfig;
use CG\Skeleton\Module\BaseConfig;
use InvalidArgumentException;
use SplObjectStorage;
use CG\Skeleton\Console\Lists\Commands;
use CG\Skeleton\Module\Command\Enable;
use CG\Skeleton\Module\Command\Disable;

abstract class AbstractModule implements ModuleInterface
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

    public function getName()
    {
        return $this->getModuleName() . ' Module';
    }

    public function getConfigClass()
    {
        return 'CG\\Skeleton\\Module\\BaseConfig';
    }

    protected function validateConfig(BaseConfig $moduleConfig = null)
    {
        if (!is_a($moduleConfig, $this->getConfigClass())) {
            throw new InvalidArgumentException('$moduleConfig should be an instance of ' . $this->getConfigClass());
        }
    }

    public function run(Arguments $arguments, SkeletonConfig $config, BaseConfig $moduleConfig = null)
    {
        $this->validateConfig($moduleConfig);

        $commands = new SplObjectStorage();
        if ($this instanceof EnableInterface) {
            $commands->attach(new Enable($this, $moduleConfig));
        }
        if ($this instanceof DisableInterface) {
            $commands->attach(new Disable($this, $moduleConfig));
        }

        while ($this->commandList($commands, $arguments, $config, $moduleConfig));
    }

    protected function commandList(SplObjectStorage $commands, Arguments $arguments, SkeletonConfig $config, BaseConfig $moduleConfig)
    {
        if ($moduleConfig->isEnabled()) {
            $status = Console::COLOR_GREEN . 'Enabled' . Console::COLOR_RESET;
        } else {
            $status = Console::COLOR_RED . 'Disabled' . Console::COLOR_RESET;
        }

        $commandList = new Commands($this->getConsole(), $commands, $this->getName() . ' [' . $status . ']');
        return $commandList->askAndRun($arguments, $config);
    }
}