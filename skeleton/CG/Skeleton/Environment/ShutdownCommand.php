<?php
namespace CG\Skeleton\Environment;

use CG\Skeleton\DevelopmentEnvironment\Environment;
use CG\Skeleton\ShutdownCommandInterface;
use CG\Skeleton\Console\Shutdown;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config;
use Zend\Config\Factory as ConfigFactory;

class ShutdownCommand implements ShutdownCommandInterface
{
    protected $console;

    public function __construct(Shutdown $console)
    {
        $this->setConsole($console);
    }

    public function setConsole(Shutdown $console)
    {
        $this->console = $console;
        return $this;
    }

    public function getConsole()
    {
        return $this->console;
    }

    public function run(Arguments $arguments, Config $config, Environment $environment)
    {
        $this->getConsole()->writeStatus('Saving configuration to \'' . SKELETON_CONFIG . '\'');
        ConfigFactory::toFile(SKELETON_CONFIG, $config);
    }
}