<?php
namespace CG\Hermes\Request;

use CG\Hermes\RequestInterface;
use CG\Hermes\Response\Generic as Response;

class Generic implements RequestInterface
{
    /** @var string */
    protected $method;
    /** @var string */
    protected $uri;
    /** @var string */
    protected $body;

    public function __construct(string $method, string $uri, string $body)
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->body = $body;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function asXML(): string
    {
        return $this->body;
    }

    public function getResponseClass(): string
    {
        return Response::class;
    }

}