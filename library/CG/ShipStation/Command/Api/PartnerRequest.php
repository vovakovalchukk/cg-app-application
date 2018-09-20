<?php
namespace CG\ShipStation\Command\Api;

use CG\ShipStation\Request\PartnerRequestAbstract;

class PartnerRequest extends PartnerRequestAbstract
{
    /** @var string */
    protected $uri;
    /** @var string */
    protected $method;
    /** @var string|null */
    protected $payload;

    public function __construct(string $uri, string $method, ?string $payload = null)
    {
        $this->uri = $uri;
        $this->method = $method;
        $this->payload = $payload;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function toArray(): array
    {
        return json_decode($this->payload,true) ?? [];
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