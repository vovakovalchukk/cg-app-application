<?php
namespace CG\Skeleton\Module\Command;

use CG\Skeleton\CommandInterface;
use CG\Skeleton\Module\DisableInterface;
use CG\Skeleton\Module\ApplyConfigurationInterface;
use CG\Skeleton\Module\BaseConfig;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config;
use CG\Skeleton\Console;

class Disable implements CommandInterface
{
    protected $disableInterface;
    protected $moduleConfig;

    public function __construct(DisableInterface $disableInterface, BaseConfig $moduleConfig)
    {
        $this->setDisableInterface($disableInterface)->setModuleConfig($moduleConfig);
    }

    public function setDisableInterface(DisableInterface $disableInterface)
    {
        $this->disableInterface = $disableInterface;
        return $this;
    }

    public function getDisableInterface()
    {
        return $this->disableInterface;
    }

    public function setModuleConfig(BaseConfig $moduleConfig)
    {
        $this->moduleConfig = $moduleConfig;
        return $this;
    }

    public function getModuleConfig()
    {
        return $this->moduleConfig;
    }

    public function getName()
    {
        return 'Disable Module';
    }

    public function run(Arguments $arguments, Config $config)
    {
        $this->getDisableInterface()->disable($arguments, $config, $this->getModuleConfig());
        if ($this->getDisableInterface() instanceof ApplyConfigurationInterface) {
            $this->getDisableInterface()->applyConfiguration($arguments, $config, $this->getModuleConfig());
        }
    }
}