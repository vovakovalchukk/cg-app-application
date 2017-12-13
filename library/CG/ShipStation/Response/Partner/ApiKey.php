<?php
namespace CG\ShipStation\Response\Partner;

use CG\ShipStation\EntityTrait\AccountTrait;
use CG\ShipStation\EntityTrait\TimestampableTrait;
use CG\ShipStation\ResponseAbstract;
use CG\Stdlib\DateTime;

class ApiKey extends ResponseAbstract
{
    use TimestampableTrait;
    use AccountTrait;

    /** @var  string */
    protected $encryptedApiKey;
    /** @var  string */
    protected $description;
    /** @var  int */
    protected $apiKeyId;

    protected function build($decodedJson)
    {
        return $this->setEncryptedApiKey($decodedJson->encrypted_api_key)
            ->setCreatedAt(new DateTime($decodedJson->created_at))
            ->setDescription($decodedJson->description)
            ->setAccountId($decodedJson->account_id)
            ->setApiKeyId($decodedJson->api_key_id);
    }

    public function getEncryptedApiKey(): string
    {
        return $this->encryptedApiKey;
    }

    public function setEncryptedApiKey(string $encryptedApiKey)
    {
        $this->encryptedApiKey = $encryptedApiKey;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description)
    {
        $this->description = $description;
        return $this;
    }

    public function getApiKeyId(): int
    {
        return $this->apiKeyId;
    }

    public function setApiKeyId(int $apiKeyId)
    {
        $this->apiKeyId = $apiKeyId;
        return $this;
    }
}
