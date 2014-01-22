<?php
namespace CG\Skeleton\DevelopmentEnvironment;

use CG\Skeleton\StartupCommandInterface;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config as SkeletonConfig;
use CG\Skeleton\DevelopmentEnvironment\CommandTrait;
use Zend\Config\Config as ZendConfig;
use CG\Skeleton\Console\Startup;

class StartupCommand implements StartupCommandInterface
{
    use CommandTrait;
    use \CG\Skeleton\GitTicketIdTrait;

    protected $defaults;
    protected $console;

    public function __construct(Startup $console)
    {
        $this->setConsole($console);
        $this->defaults = array();
    }

    public function setConsole(Startup $console)
    {
        $this->console = $console;
        return $this;
    }

    public function getConsole()
    {
        return $this->console;
    }

    public function runCommands(Arguments $arguments, SkeletonConfig $config, Environment $environment)
    {
        $this->setupEnvironment($config);
    }

    protected function setupEnvironment(SkeletonConfig $config)
    {

    }
}