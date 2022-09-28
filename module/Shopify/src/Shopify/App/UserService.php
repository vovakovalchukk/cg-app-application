<?php
namespace Shopify\App;

use Predis\Client as Predis;

class UserService
{
    protected const CACHE_KEY = 'Shopify:Accounts';

    /** @var Predis */
    protected $predis;

    public function __construct(Predis $predis)
    {
        $this->predis = $predis;
    }

    public function saveAccountId(int $userId, int $accountId = null): void
    {
        if (is_null($accountId)) {
            return;
        }

        $this->predis->hset(static::CACHE_KEY, $userId, $accountId);
    }

    public function getAccountId(int $userId): ?int
    {
        return $this->predis->hget(static::CACHE_KEY, $userId);
    }

    public function removeAccountId(int $userId): void
    {
        $this->predis->hdel(static::CACHE_KEY, $userId);
    }
}