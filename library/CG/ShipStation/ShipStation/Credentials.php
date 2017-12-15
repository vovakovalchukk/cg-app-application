<?php
namespace CG\ShipStation\ShipStation;

use CG\Account\CredentialsInterface;

class Credentials implements CredentialsInterface
{
    protected $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function isEmpty()
    {
        return empty($this->apiKey);
    }

    public function toArray()
    {
        return ['apiKey' => $this->getApiKey()];
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }
}
