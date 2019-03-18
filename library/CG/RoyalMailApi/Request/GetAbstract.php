<?php
namespace CG\RoyalMailApi\Request;

use CG\RoyalMailApi\RequestInterface;

abstract class GetAbstract implements RequestInterface
{
    public function getMethod(): string
    {
        return 'GET';
    }

    public function getAdditionalHeaders(): array
    {
        return [];
    }

    public function jsonSerialize()
    {
        return null;
    }
}