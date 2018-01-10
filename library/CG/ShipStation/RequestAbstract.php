<?php
namespace CG\ShipStation;

abstract class RequestAbstract implements RequestInterface
{
    const URI = '';
    const URI_VERSION_PREFIX = '/v1';
    const METHOD = 'GET';

    abstract public function toArray(): array;

    public function getUri(): string
    {
        return static::URI_VERSION_PREFIX . static::URI;
    }

    public function getMethod(): string
    {
        return static::METHOD;
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}
