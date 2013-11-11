<?php
namespace CG\Skeleton\DevelopmentEnvironment;

use Zend\Config\Config as ZendConfig;

class Config extends ZendConfig
{
    const IP = 'IP';
    const HOSTNAME = 'hostname';
    const DOMAIN = 'channelgrabber.com';

    public function getIp()
    {
        return $this->get(static::IP);
    }

    public function setIp($ip)
    {
        $this->offsetSet(static::IP, $ip);
        return $this;
    }

    public function getHostname($appName)
    {
        return $this->get(static::HOSTNAME, $appName . '.' . static::DOMAIN);
    }

    public function setHostname($hostname)
    {
        $this->offsetSet(static::HOSTNAME, $hostname);
        return $this;
    }
}