<?php
namespace CG\Skeleton\Module\Redis;

use CG\Skeleton\Module\BaseConfig;
use Zend\Config\Config as ZendConfig;

class Config extends BaseConfig
{
    const REDIS_ADAPTERS = 'Adapters';

    public function getRedisAdapters()
    {
        $redisAdapters = $this->get(static::REDIS_ADAPTERS, array());
        if (is_string($redisAdapters)) {
            $redisAdapters = array($redisAdapters);
        } else if ($redisAdapters instanceof ZendConfig) {
            $redisAdapters = $redisAdapters->toArray();
        }
        return $redisAdapters;
    }

    public function setRedisAdapters(array $redisAdapters)
    {
        $this->offsetSet(static::REDIS_ADAPTERS, $redisAdapters);
        return $this;
    }
}