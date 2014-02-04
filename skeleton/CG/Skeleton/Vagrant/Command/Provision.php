<?php
namespace CG\Skeleton\Vagrant\Command;

use CG\Skeleton\CommandInterface;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config;
use CG\Skeleton\Vagrant\CommandTrait;
use CG\Skeleton\DevelopmentEnvironment\Environment;

class Provision implements CommandInterface
{
    use CommandTrait;

    public function getName()
    {
        return 'Provision Virtual Machine';
    }

    protected function runCommands(Arguments $arguments, Config $config, Environment $environment)
    {
        $environment->vagrantProvision($this->getConsole());
    }
}