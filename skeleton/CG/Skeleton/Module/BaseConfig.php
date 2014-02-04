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

    public function setComposerRequire($name, $version)
    {
        $formattedName = str_replace("/", "-", $name);
        $this->offsetSet($formattedName, $version);
    }

    public function getComposerRequireVersion($name)
    {
        $formattedName = str_replace("/", "-", $name);
        return $this->get($formattedName);
    }

    public function removeComposerRequire($name)
    {
        $formattedName = str_replace("/", "-", $name);
        $this->offsetUnset($formattedName);
    }
}