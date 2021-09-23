<?php
namespace CG\UkMail\Request;

abstract class AbstractRequest
{
    protected const METHOD = 'GET';
    protected const URI = '';

    public function getMethod(): string
    {
        return static::METHOD;
    }

    public function getUri(): string
    {
        return static::URI;
    }

    public function getOptions(array $defaultOptions = []): array
    {
        return $defaultOptions;
    }
}