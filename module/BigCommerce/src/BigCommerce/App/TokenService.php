<?php
namespace BigCommerce\App;

use Predis\Client as Predis;

class TokenService
{
    const CACHE_KEY_PREFIX = 'BigCommerce:Token:';

    /** @var Predis $predis */
    protected $predis;

    public function __construct(Predis $predis)
    {
        $this->setPredis($predis);
    }

    protected function getCacheKey($shopHash)
    {
        return static::CACHE_KEY_PREFIX . $shopHash;
    }

    public function storeToken($shopHash, $token, array $additionalInfo = [])
    {
        $caheKey = $this->getCacheKey($shopHash);
        $transaction = $this->predis->transaction()->del($caheKey)->hset($caheKey, 'token', $token);
        foreach ($additionalInfo as $key => $info) {
            $transaction->hset($caheKey, $key, json_encode($info));
        }
        $transaction->execute();
    }

    public function fetchToken($shopHash, array &$additionalInfo = null)
    {
        $data = $this->predis->hgetall($this->getCacheKey($shopHash));
        if (!isset($data['token'])) {
            return null;
        }

        $token = $data['token'];
        unset($data['token']);

        $additionalInfo = [];
        foreach ($data as $key => $json) {
            $additionalInfo[$key] = json_decode($json, true);
        }

        return $token;
    }

    /**
     * @return self
     */
    protected function setPredis(Predis $predis)
    {
        $this->predis = $predis;
        return $this;
    }
}
