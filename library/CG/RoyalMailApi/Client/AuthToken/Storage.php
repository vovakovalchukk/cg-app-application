<?php
namespace CG\RoyalMailApi\Client\AuthToken;

use CG\CourierAdapter\Account;
use CG\RoyalMailApi\Client\AuthToken;
use DateTime;
use Predis\Client as PredisClient;

class Storage
{
    const KEY_PREFIX = 'RoyalMailApiAuthToken:';

    /** @var PredisClient */
    protected $predisClient;

    public function __construct(PredisClient $predisClient)
    {
        $this->predisClient = $predisClient;
    }

    public function fetchForAccount(Account $account): ?AuthToken
    {
        $key = $this->getKeyForAccount($account);
        $token = $this->predisClient->get($key);
        if (!$token) {
            return null;
        }
        $ttl = $this->predisClient->ttl($key);
        $expires = new DateTime('@' . (time() + $ttl));
        return new AuthToken($token, $expires);
    }

    public function saveForAccount(AuthToken $accessToken, Account $account): void
    {
        $key = $this->getKeyForAccount($account);
        $ttl = $accessToken->getExpires()->getTimestamp() - time();
        $this->predisClient->setex($key, $ttl, $accessToken->getToken());
    }

    protected function getKeyForAccount(Account $account): string
    {
        return static::KEY_PREFIX . $account->getId();
    }
}