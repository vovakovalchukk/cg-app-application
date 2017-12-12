<?php
namespace CG\ShipStation;

abstract class RequestAbstract implements RequestInterface
{
    const URI = '';
    const API_VERSION = '/v1';
    const METHOD = 'POST';

    abstract public function toArray(): array;

    public function getUri(): string
    {
        return static::API_VERSION . static::URI;
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
