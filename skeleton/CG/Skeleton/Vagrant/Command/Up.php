<?php
namespace CG\Skeleton\Vagrant\Command;

use CG\Skeleton\CommandInterface;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config;
use CG\Skeleton\Vagrant\CommandTrait;
use CG\Skeleton\DevelopmentEnvironment\Environment;

class Up implements CommandInterface
{
    use CommandTrait;

    public function getName()
    {
        return 'Start Virtual Machine';
    }

    protected function runCommands(Arguments $arguments, Config $config, Environment $environment)
    {
        $environment->vagrantUp($this->getConsole());
    }
}