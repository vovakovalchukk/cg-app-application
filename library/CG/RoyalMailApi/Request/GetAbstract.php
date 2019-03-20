<?php
namespace CG\RoyalMailApi\Request;

use CG\CourierAdapter\Account;
use CG\RoyalMailApi\Credentials;
use CG\RoyalMailApi\RequestInterface;

abstract class GetAbstract implements RequestInterface
{
    public function getMethod(): string
    {
        return 'GET';
    }

    public function getAdditionalHeaders(Account $account, Credentials $credentials): array
    {
        return [];
    }

    public function jsonSerialize()
    {
        return null;
    }
}