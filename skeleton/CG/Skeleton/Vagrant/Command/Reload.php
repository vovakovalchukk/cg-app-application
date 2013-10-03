<?php
namespace CG\Skeleton\Vagrant\Command;

use CG\Skeleton\CommandInterface;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config;
use CG\Skeleton\Vagrant\CommandTrait;

class Reload implements CommandInterface
{
    use CommandTrait;

    public function getName()
    {
        return 'Restart Virtual Machine';
    }

    protected function runCommands(Arguments $arguments, Config $config)
    {
        passthru('vagrant reload ' . $config->getNode());
    }
}