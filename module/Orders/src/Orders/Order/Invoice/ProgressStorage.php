<?php
namespace Orders\Order\Invoice;

use Predis\Client as Predis;

class ProgressStorage
{
    const KEY_PREFIX = 'OrderInvoiceProgress:';
    const KEY_EXPIRY_SEC = 86400; // 1 day

    protected $predis;

    public function __construct(Predis $predis)
    {
        $this->setPredis($predis);
    }

    public function setProgress($key, $value)
    {
        $this->getPredis()->setex(static::KEY_PREFIX.$key, static::KEY_EXPIRY_SEC, $value);
        return $this;
    }

    public function getProgress($key)
    {
        return $this->getPredis()->get(static::KEY_PREFIX.$key);
    }

    protected function getPredis()
    {
        return $this->predis;
    }

    protected function setPredis(Predis $predis)
    {
        $this->predis = $predis;
        return $this;
    }
}