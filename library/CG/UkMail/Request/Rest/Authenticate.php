<?php
namespace CG\UkMail\Request\Rest;

use CG\UkMail\Request\AbstractRequest;
use CG\UkMail\Response\Rest\Authenticate as Response;

class Authenticate extends AbstractRequest implements RequestInterface
{
    protected const URI = 'gateway/SSOAuthenticationAPI/1.0/ssoAuth/users/authenticate';

    protected $apiKey;
    protected $username;
    protected $password;

    public function __construct(string $apiKey, string $username, string $password)
    {
        $this->apiKey = $apiKey;
        $this->username = $username;
        $this->password = $password;
    }

    public function getOptions(array $defaultOptions = []): array
    {
        $options = parent::getOptions($defaultOptions);
        return [
            'headers' => array_merge($options['headers'] ?? [], $this->getHeaders()),
            'query' => array_merge($options['query'] ?? [], $this->getQuery())
        ];
    }

    public function getResponseClass(): string
    {
        return Response::class;
    }

    protected function getHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'x-api-key' => $this->apiKey
        ];
    }

    protected function getQuery(): array
    {
        return [
            'username' => $this->username,
            'password' => $this->password
        ];
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function setApiKey(string $apiKey): Authenticate
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): Authenticate
    {
        $this->username = $username;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): Authenticate
    {
        $this->password = $password;
        return $this;
    }
}