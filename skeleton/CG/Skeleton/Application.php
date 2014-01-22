<?php
namespace CG\Skeleton;

use CG\Skeleton\Vagrant\NodeData;
use SplObjectStorage;
use CG\Skeleton\Console\Lists\Commands;
use CG\Skeleton\Console;
use CG\Skeleton\DevelopmentEnvironment\EnvironmentFactory;

class Application
{
    protected $console;
    protected $arguments;
    protected $config;
    protected $startupCommands;
    protected $commands;
    protected $shutdownCommands;
    protected $environment;

    public function __construct(Console $console, Arguments $arguments, Config $config, NodeData $nodeData)
    {
        $this->setConsole($console)
             ->setArguments($arguments)
             ->setConfig($config)
             ->setEnvironment($config, $nodeData);
        $this->startupCommands = new SplObjectStorage();
        $this->commands = new SplObjectStorage();
        $this->shutdownCommands = new SplObjectStorage();
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

    public function setEnvironment(Config $config, NodeData $nodeData)
    {
        $this->environment = EnvironmentFactory::build($config, $nodeData);
        return $this;
    }

    public function getEnvironment()
    {
        return $this->environment;
    }

    public function setArguments(Arguments $arguments)
    {
        $this->arguments = $arguments;
        return $this;
    }

    public function getArguments()
    {
        return $this->arguments;
    }

    public function setConfig(Config $config)
    {
        $this->config = $config;
        return $this;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function addStartupCommand(StartupCommandInterface $startupCommand)
    {
        $this->startupCommands->attach($startupCommand);
        return $this;
    }

    public function getStartupCommands()
    {
        return $this->startupCommands;
    }

    public function addCommand(CommandInterface $command)
    {
        $this->commands->attach($command);
        return $this;
    }

    public function getCommands()
    {
        return $this->commands;
    }

    public function addShutdownCommand(ShutdownCommandInterface $shutdownCommand)
    {
        $this->shutdownCommands->attach($shutdownCommand);
        return $this;
    }

    public function getShutdownCommands()
    {
        return $this->shutdownCommands;
    }

    public function run()
    {
        $this->getConsole()->clear();
        $this->getConsole()->writeln(Console::COLOR_BLUE . 'Welcome to the Skeleton Application Setup' . Console::COLOR_RESET);

        $this->startup();
        while ($this->commandList());
        $this->shutdown();
    }

    public function startup()
    {
        $startupCommands = $this->getStartupCommands();
        if ($startupCommands->count() > 0) {
            $this->getConsole()->writeln();
            foreach ($startupCommands as $command) {
                $command->run($this->getArguments(), $this->getConfig(), $this->getEnvironment());
            }
            $this->getConsole()->writeln();
        }
    }

    public function getHeader()
    {
        return "Application:" . CONSOLE::COLOR_LIGHT_BLUE . $this->getConfig()->getAppName() . CONSOLE::COLOR_RESET
               . " Environment:" . CONSOLE::COLOR_CYAN . $this->getConfig()->getEnvironment() . CONSOLE::COLOR_RESET;
    }

    public function shutdown()
    {
        $shutdownCommands = $this->getShutdownCommands();
        if ($shutdownCommands->count() > 0) {
            foreach ($shutdownCommands as $command) {
                $command->run($this->getArguments(), $this->getConfig(), $this->getEnvironment());
            }
        }
        exit();
    }

    protected function commandList()
    {
        $commandList = new Commands($this->getConsole(), $this->getCommands(), $this->getHeader());
        return $commandList->askAndRun($this->getArguments(), $this->getConfig(), $this->getEnvironment());
    }
}