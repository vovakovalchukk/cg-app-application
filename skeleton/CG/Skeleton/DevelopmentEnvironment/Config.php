<?php
namespace CG\Skeleton\DevelopmentEnvironment;

use Zend\Config\Config as ZendConfig;
use CG\Skeleton\Config as SkeletonConfig;

class Config extends ZendConfig
{
    const IP = 'IP';
    const HOSTNAME = 'hostname';
    const DOMAIN = 'channelgrabber.com';
    const NODE = 'node';

    public function getIp()
    {
        return $this->get(static::IP);
    }

    public function setIp($ip)
    {
        $this->offsetSet(static::IP, $ip);
        return $this;
    }

    public function getHostname(SkeletonConfig $config, Environment $environment)
    {
        return $this->get(static::HOSTNAME, $config->getAppName() . '.' . static::DOMAIN . $environment->getSuffix());
    }

    public function setHostname($hostname)
    {
        $this->offsetSet(static::HOSTNAME, $hostname);
        return $this;
    }

    public function getNode()
    {
        return $this->get(static::NODE);
    }

    public function setNode($node)
    {
        $this->offsetSet(static::NODE, $node);
        return $this;
    }
}