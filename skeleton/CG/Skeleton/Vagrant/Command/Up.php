<?php
namespace CG\Skeleton\Vagrant\Command;

use CG\Skeleton\CommandInterface;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config;
use CG\Skeleton\Vagrant\CommandTrait;

class Up implements CommandInterface
{
    use CommandTrait;

    public function getName()
    {
        return 'Start Virtual Machine';
    }

    protected function runCommands(Arguments $arguments, Config $config)
    {
        passthru('vagrant up ' . $config->getNode());
    }
}