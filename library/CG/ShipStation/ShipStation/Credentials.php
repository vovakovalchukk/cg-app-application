<?php
namespace CG\ShipStation\ShipStation;

use CG\Account\CredentialsInterface;

class Credentials implements CredentialsInterface
{
    protected $apiKey = '';

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

    public function setApiKey(string $apiKey)
    {
        $this->apiKey = $apiKey;
        return $this;
    }
}
