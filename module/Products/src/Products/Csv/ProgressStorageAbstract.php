<?php
namespace Products\Csv;

use Predis\Client as Predis;

abstract class ProgressStorageAbstract
{
    /** @var string */
    const KEY_PREFIX = '';
    /** @var string */
    const KEY_PREFIX_TOTAL = '';
    /** @var int */
    const KEY_EXPIRY_SEC = 30;

    /** @var Predis */
    protected $predis;

    public function __construct(Predis $predis)
    {
        $this->setPredis($predis);
    }

    public function setProgress($key, $value, $total = null)
    {
        $this->predis->setex(static::KEY_PREFIX . $key, static::KEY_EXPIRY_SEC, $value);
        if ($total) {
            $this->predis->setex(static::KEY_PREFIX . static::KEY_PREFIX_TOTAL . $key, static::KEY_EXPIRY_SEC, $total);
        }
        return $this;
    }

    public function getProgress($key)
    {
        return $this->predis->get(static::KEY_PREFIX . $key);
    }

    public function incrementProgress($key, $increment, $total = null)
    {
        $count = $this->getProgress($key);
        $this->setProgress($key, $count + $increment, $total);
    }

    public function getTotal($key)
    {
        return $this->predis->get(static::KEY_PREFIX . static::KEY_PREFIX_TOTAL . $key);
    }

    public function removeProgress($key)
    {
        $this->predis->del(static::KEY_PREFIX . $key);
        $this->predis->del(static::KEY_PREFIX . static::KEY_PREFIX_TOTAL . $key);
    }

    protected function setPredis(Predis $predis)
    {
        $this->predis = $predis;
        return $this;
    }
}