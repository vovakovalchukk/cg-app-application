<?php
namespace CG\UkMail\Request;

abstract class AbstractRequest
{
    protected const METHOD = 'GET';
    protected const URI = '';

    public function getMethod()
    {
        return static::METHOD;
    }

    abstract public function getUri();

    public function getOptions(array $defaultOptions = []): array
    {
        return $defaultOptions;
    }
}