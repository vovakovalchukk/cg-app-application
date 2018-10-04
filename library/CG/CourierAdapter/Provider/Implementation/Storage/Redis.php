<?php
namespace CG\CourierAdapter\Provider\Implementation\Storage;

use CG\CourierAdapter\StorageInterface;
use Predis\Client as PredisClient;
use CG\Predis\Command\Setnxex;

class Redis implements StorageInterface
{
    const KEY_PREFIX = 'CourierAdapter:';

    /** @var PredisClient */
    protected $predisClient;

    public function __construct(PredisClient $predisClient)
    {
        $this->setPredisClient($predisClient);
        $this->predisClient->getProfile()->defineCommand('setnxex', Setnxex::class);
    }

    /**
     * @return self
     */
    public function set($key, $data)
    {
        // Prefix the key to act as a namespace so implementers don't accidentally overwrite our other values in Redis
        $this->predisClient->set(static::KEY_PREFIX . $key, $data);
        return $this;
    }

    /**
     * @return mixed
     */
    public function get($key)
    {
        return $this->predisClient->get(static::KEY_PREFIX . $key);
    }

    /**
     * @return self
     */
    public function remove($key)
    {
        $this->predisClient->del(static::KEY_PREFIX . $key);
        return $this;
    }

    protected function setPredisClient(PredisClient $predisClient)
    {
        $this->predisClient = $predisClient;
        return $this;
    }

    public function setnxex($key, $time, $expiry)
    {
        return $this->predisClient->setnxex(static::KEY_PREFIX . $key, $time, $expiry);
    }
}