<?php
namespace CG\Skeleton\Console\Lists;

use CG\Skeleton\Console;
use SplObjectStorage;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config;
use CG\Skeleton\CommandInterface;
use CG\Skeleton\Module\Config as ModuleConfig;
use CG\Skeleton\Module\ModuleInterface;
use InvalidArgumentException;
use CG\Skeleton\DevelopmentEnvironment\Environment;

class Modules extends Commands
{
    const TAGLINE = 'The following modules are available:';

    protected $moduleConfig;
    protected $environment;

    public function __construct(Console $console, SplObjectStorage $commands, ModuleConfig $moduleConfig, Environment $environment)
    {
        $this->setModuleConfig($moduleConfig)
             ->setEnvironment($environment);
        parent::__construct($console, $commands);
    }

    public function setEnvironment($environment)
    {
        $this->environment = $environment;
        return $this;
    }

    public function getEnvironment()
    {
        return $this->environment;
    }

    public function setModuleConfig(ModuleConfig $moduleConfig)
    {
        $this->moduleConfig = $moduleConfig;
        return $this;
    }

    public function getModuleConfig()
    {
        return $this->moduleConfig;
    }

    protected function getDisplayName(CommandInterface $command)
    {
        if (!($command instanceof ModuleInterface)) {
            throw new InvalidArgumentException('$command should be an instance of CG\Skeleton\Module\ModuleInterface');
        }

        $moduleConfig = $this->getModuleConfig()->getModule($command->getModuleName());
        if ($moduleConfig->isEnabled()) {
            $status = Console::COLOR_GREEN . 'Enabled' . Console::COLOR_RESET;
        } else {
            $status = Console::COLOR_RED . 'Disabled' . Console::COLOR_RESET;
        }

        return $command->getName() . ' [' . $status . ']';
    }

    protected function runCommand(Arguments $arguments, Config $config, CommandInterface $command)
    {
        if (!($command instanceof ModuleInterface)) {
            throw new InvalidArgumentException('$command should be an instance of CG\Skeleton\Module\ModuleInterface');
        }

        $moduleConfig = $this->getModuleConfig()->getModule($command->getModuleName());
        $command->run($arguments, $config, $this->getEnvironment(), $moduleConfig);
        $this->getModuleConfig()->setModule($command->getModuleName(), $moduleConfig);
    }
}