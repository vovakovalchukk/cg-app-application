<?php
namespace CG\RoyalMailApi\Request;

use CG\CourierAdapter\Account;
use CG\RoyalMailApi\Credentials;
use CG\RoyalMailApi\RequestInterface;
use CG\RoyalMailApi\Response\Generic as Response;

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
        $this->uri = ltrim($uri, '/');
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

    public function jsonSerialize()
    {
        return json_decode($this->body);
    }

    public function getAdditionalHeaders(Account $account, Credentials $credentials): array
    {
        return [];
    }

    public function getResponseClass(): string
    {
        return Response::class;
    }
}