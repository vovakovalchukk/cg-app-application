<?php
namespace CG\ShipStation\Command;

use CG\ShipStation\RequestAbstract;

class Request extends RequestAbstract
{
    protected $uri;
    protected $method;
    protected $payload;

    public function __construct(string $uri, string $method, $payload)
    {
        $this->uri = $uri;
        $this->payload = $payload;
        $this->method = $method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function toArray(): array
    {
        return json_decode($this->payload,1) ?? [];
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getResponseClass(): string
    {
        return Response::class;
    }
}
