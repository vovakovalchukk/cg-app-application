<?php
namespace CG\Skeleton\Module\Mongo;

use CG\Skeleton\Module\BaseConfig;
use Zend\Config\Config as ZendConfig;

class Config extends BaseConfig
{
    const MONGO_ADAPTERS = 'Adapters';

    public function getMongoAdapters()
    {
        $mongoAdapters = $this->get(static::MONGO_ADAPTERS, array());
        if (is_string($mongoAdapters)) {
            $mongoAdapters = array($mongoAdapters);
        } else if ($mongoAdapters instanceof ZendConfig) {
            $mongoAdapters = $mongoAdapters->toArray();
        }
        return $mongoAdapters;
    }

    public function setMongoAdapters(array $mongoAdapters)
    {
        $this->offsetSet(static::MONGO_ADAPTERS, $mongoAdapters);
        return $this;
    }
}