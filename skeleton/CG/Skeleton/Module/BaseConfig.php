<?php
namespace CG\Skeleton\Module;

use Zend\Config\Config as ZendConfig;

class BaseConfig extends ZendConfig
{
    const ENABLED = 'enabled';

    public function isEnabled()
    {
        return (boolean) $this->get(static::ENABLED, false);
    }

    public function setEnabled($enabled)
    {
        $this->offsetSet(static::ENABLED, (boolean) $enabled);
    }
}