<?php
namespace CG\Skeleton\DevelopmentEnvironment;

use CG\Skeleton\Config as SkeletonConfig;
use CG\Skeleton\Chef\StartupCommand;
use CG\Skeleton\Chef\Hosts;
use CG\Skeleton\Vagrant\NodeData;

abstract class Environment implements EnvironmentInterface {

    protected $skeletonConfig;
    protected $environmentConfig;
    protected $nodeData;

    public function __construct(SkeletonConfig $config, NodeData $nodeData)
    {
        $this->setConfig($config)
             ->setEnvironmentConfig()
             ->setNodeData($nodeData);
    }

    public function getNodeData()
    {
        return $this->nodeData;
    }

    public function setNodeData($nodeData)
    {
        $this->nodeData = $nodeData;
        return $this;
    }

    public function getEnvironmentConfig()
    {
        return $this->environmentConfig;
    }

    protected function setEnvironmentConfig()
    {
        $environmentConfig = $this->skeletonConfig->get('Environment', new Config(array(), true));
        $this->environmentConfig = $environmentConfig->get($this->skeletonConfig->getEnvironment(), new Config(array(), true));

        $environmentConfig->offsetSet($this->skeletonConfig->getEnvironment(), $this->environmentConfig);
        $this->skeletonConfig->offsetSet('Environment', $environmentConfig);
        return $this;
    }

    public function getConfig()
    {
        return $this->skeletonConfig;
    }

    public function setConfig(SkeletonConfig $config)
    {
        $this->skeletonConfig = $config;
        return $this;
    }

    protected function getHosts()
    {
        $hostsFile = StartupCommand::HOSTS . strtolower($this->getName()) . '.json';
        $hosts = new Hosts($hostsFile, $this->getName());
        return $hosts->getData()['hosts'];
    }

    protected function updateHostsFileEntry($ip, $hostname)
    {
        $hostEntry = $ip . ' ' . $hostname;
        exec(
            'if grep -q "' . $hostEntry . '" /etc/hosts; then return; fi;'
            . 'grep -q "' . $hostname . '" /etc/hosts'
            . ' && sudo sed -i \'/' . $hostname . '$/c\\' . $hostEntry . '\' /etc/hosts'
            . ' || echo "' . $hostEntry . '" | sudo tee -a /etc/hosts;'
        );
    }

}