<?php
namespace BigCommerce\App;

use Predis\Client as Predis;

class TokenService
{
    const CACHE_KEY_PREFIX = 'BigCommerce:Token:';
    const CACHE_FIELD_TOKEN = 'token';

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

    public function hasToken($shopHash)
    {
        return (bool) $this->predis->hexists($this->getCacheKey($shopHash), static::CACHE_FIELD_TOKEN);
    }

    public function storeToken($shopHash, $token, array $additionalInfo = [])
    {
        $caheKey = $this->getCacheKey($shopHash);
        $transaction = $this->predis->transaction()->del($caheKey)->hset($caheKey, static::CACHE_FIELD_TOKEN, $token);
        foreach ($additionalInfo as $key => $info) {
            $transaction->hset($caheKey, $key, json_encode($info));
        }
        $transaction->execute();
    }

    public function fetchToken($shopHash, array &$additionalInfo = null)
    {
        $caheKey = $this->getCacheKey($shopHash);
        list($data, ) = $this->predis->transaction()->hgetall($caheKey)->del($caheKey)->execute();
        if (!isset($data[static::CACHE_FIELD_TOKEN])) {
            return null;
        }

        $token = $data[static::CACHE_FIELD_TOKEN];
        unset($data[static::CACHE_FIELD_TOKEN]);

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
