<?php
namespace CG\Intersoft\Request;

use CG\CourierAdapter\Account;
use CG\Intersoft\Credentials;
use CG\Intersoft\RequestAbstract;
use CG\Intersoft\RequestInterface;
use CG\Intersoft\Response\Generic as Response;
use SimpleXMLElement;

class Generic extends RequestAbstract
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

    public function getResponseClass(): string
    {
        return Response::class;
    }

    public function asXml(): string
    {
        $xml = new SimpleXMLElement($this->body);
        $xml = $this->addIntegrationHeader($xml);
        return $xml;
    }
}