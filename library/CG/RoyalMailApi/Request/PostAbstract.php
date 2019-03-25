<?php
namespace CG\RoyalMailApi\Request;

use CG\CourierAdapter\Account;
use CG\RoyalMailApi\Credentials;
use CG\RoyalMailApi\RequestInterface;

abstract class PostAbstract implements RequestInterface
{
    public function getMethod(): string
    {
        return 'POST';
    }

    public function getAdditionalHeaders(Account $account, Credentials $credentials): array
    {
        return [];
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    abstract protected function toArray(): array;
}