<?php
namespace CG\RoyalMailApi\Request;

use CG\CourierAdapter\Account;
use CG\RoyalMailApi\Credentials;
use CG\RoyalMailApi\RequestInterface;

abstract class DeleteAbstract implements RequestInterface
{
    public function getMethod(): string
    {
        return 'DELETE';
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