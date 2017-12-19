<?php
namespace CG\ShipStation\Response\Partner;

use CG\ShipStation\Messages\Account as AccountEntity;
use CG\ShipStation\Messages\Timestamp;
use CG\ShipStation\ResponseAbstract;

class ApiKey extends ResponseAbstract
{
    /** @var  AccountEntity */
    protected $account;
    /** @var  Timestamp */
    protected $timestamp;
    /** @var  string */
    protected $encryptedApiKey;
    /** @var  string */
    protected $description;
    /** @var  int */
    protected $apiKeyId;

    public function __construct(
        AccountEntity $account,
        Timestamp $timestamp,
        string $encryptedApiKey,
        string $description,
        int $apiKeyId
    ) {
        $this->account = $account;
        $this->timestamp = $timestamp;
        $this->encryptedApiKey = $encryptedApiKey;
        $this->description = $description;
        $this->apiKeyId = $apiKeyId;
    }

    protected static function build($decodedJson)
    {
        $account = (new AccountEntity($decodedJson->account_id));
        $timestamp = (new Timestamp($decodedJson->created_at));
        return new static(
            $account,
            $timestamp,
            $decodedJson->encrypted_api_key,
            $decodedJson->description,
            $decodedJson->api_key_id
        );
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
