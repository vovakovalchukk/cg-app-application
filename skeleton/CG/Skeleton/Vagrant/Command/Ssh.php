<?php
namespace CG\Skeleton\Vagrant\Command;

use CG\Skeleton\CommandInterface;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config;
use CG\Skeleton\Vagrant\CommandTrait;
use CG\Skeleton\DevelopmentEnvironment\Environment;

class Ssh implements CommandInterface
{
    use CommandTrait;

    public function getName()
    {
        return 'SSH onto Virtual Machine';
    }

    protected function runCommands(Arguments $arguments, Config $config, Environment $environment)
    {
        $environment->vagrantSsh($this->getConsole());
    }
}