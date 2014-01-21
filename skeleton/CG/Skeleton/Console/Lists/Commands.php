<?php
namespace CG\Skeleton\Console\Lists;

use CG\Skeleton\Console;
use SplObjectStorage;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config;
use CG\Skeleton\CommandInterface;
use CG\Skeleton\DevelopmentEnvironment\Environment;

class Commands
{
    const TAGLINE = 'The following commands are available:';

    protected $console;
    protected $commands;
    protected $header;

    public function __construct(Console $console, SplObjectStorage $commands, $header = '')
    {
        $this->setConsole($console)->setCommands($commands)->setHeader($header);
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

    public function setCommands(SplObjectStorage $commands)
    {
        $this->commands = $commands;
        return $this;
    }

    public function getCommands()
    {
        return $this->commands;
    }

    public function setHeader($header)
    {
        $this->header = trim($header);
        return $this;
    }

    public function getHeader()
    {
        return $this->header;
    }

    protected function getTagLine()
    {
        $tagLine = static::TAGLINE;

        $header = $this->getHeader();
        if (strlen($header) == 0) {
            return $tagLine;
        }

        return $header . "\n" . $tagLine;
    }

    public function askAndRun(Arguments $arguments, Config $config, Environment $environment)
    {
        $commands = $this->getCommands();

        if ($commands->count() == 0) {
            $this->getConsole()->clear();
            return;
        }

        $this->getConsole()->writeln($this->getTagLine());
        $indent = 3 + strlen($commands->count());

        $i = 1;
        $commands->rewind();
        foreach ($commands as $command) {
            $this->displayCommand($indent, $i++, $this->getDisplayName($command));
        }
        $this->displayCommand($indent, 0, 'Cancel');

        $selected = null;
        while (!is_numeric($selected) || $selected < 0 || $selected > $commands->count()) {
            $this->getConsole()->write('?> ');
            $selected = $this->getConsole()->readln();
        }

        if ($selected == 0) {
            $this->getConsole()->clear();
            return;
        }

        $i = 1;
        $commands->rewind();
        foreach ($commands as $command) {
            if ($selected != $i++) {
                continue;
            }
            $this->getConsole()->clear();
            $this->runCommand($arguments, $config, $command, $environment);
        }


        return (boolean) $selected;
    }

    protected function getDisplayName(CommandInterface $command)
    {
        return $command->getName();
    }

    protected function displayCommand($indent, $index, $command)
    {
        $this->getConsole()->writeln(
            Console::COLOR_GREEN . str_pad($index, $indent, ' ', STR_PAD_LEFT) . ': '
            . Console::COLOR_RESET . $command
        );
    }

    protected function runCommand(Arguments $arguments, Config $config, CommandInterface $command,
                                  Environment $environment)
    {
        $command->run($arguments, $config, $environment);
    }
}