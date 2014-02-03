<?php
namespace CG\Skeleton\DevelopmentEnvironment\Command;

use CG\Skeleton\CommandInterface;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config;
use CG\Skeleton\Console;
use CG\Skeleton\DevelopmentEnvironment\Environment;
use CG\Skeleton\DevelopmentEnvironment\EnvironmentFactory;

class ChangeEnvironment implements CommandInterface
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
        return 'Switch development environment';
    }

    public function run(Arguments $arguments, Config $config, Environment $environment)
    {
        $availableEnvironments = EnvironmentFactory::getEnvironments();

        $this->getConsole()->writeln('Available Development Environments:');
        foreach ($availableEnvironments as $environmentName) {
            $this->getConsole()->writeln('   * ' . $environmentName);
        }

        $chosenEnvironment = ucwords(strtolower($this->getConsole()->ask('Please specify a development environment')));
        $this->getConsole()->clear();

        $config->setEnvironment($chosenEnvironment);

        $this->getConsole()->write(Console::COLOR_RED . "Warning: Skeleton must be restarted for environment changes to take effect.\n" . Console::COLOR_RESET);
    }
}