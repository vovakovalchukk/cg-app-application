<?php
namespace CG\UkMail\Request\Rest;

use CG\UkMail\Request\AbstractPostRequest;
use CG\UkMail\Response\Rest\Collection as Response;

class Collection extends AbstractPostRequest implements RequestInterface
{
    protected const URI = 'v1/collection/collectionrequests';

    /** @var string */
    protected $apiKey;
    /** @var string */
    protected $username;
    /** @var string */
    protected $authenticationToken;
    /** @var string */
    protected $accountNumber;
    /** @var string */
    protected $collectionDate;
    /** @var bool */
    protected $closedForLunch;
    /** @var string */
    protected $earliestTime;
    /** @var string */
    protected $latestTime;
    /** @var string */
    protected $specialInstructions;

    public function __construct(
        string $apiKey,
        string $username,
        string $authenticationToken,
        string $collectionDate,
        bool $closedForLunch,
        string $earliestTime,
        string $latestTime,
        string $specialInstructions
    ) {
        $this->apiKey = $apiKey;
        $this->username = $username;
        $this->authenticationToken = $authenticationToken;
        $this->collectionDate = $collectionDate;
        $this->closedForLunch = $closedForLunch;
        $this->earliestTime = $earliestTime;
        $this->latestTime = $latestTime;
        $this->specialInstructions = $specialInstructions;
    }

    public function getUri(): string
    {
        return static::URI;
    }

    protected function getBody(): array
    {
        return [
            'userName' => $this->getUsername(),
            'authenticationToken' => $this->getAuthenticationToken(),
            'accountNumber' => $this->getAccountNumber(),
            'collectionDate' => $this->getCollectionDate(),
            'closedForLunch' => $this->isClosedForLunch(),
            'earliestTime' => $this->getEarliestTime(),
            'latestTime' => $this->getLatestTime(),
            'specialInstructions' => $this->getSpecialInstructions(),
        ];
    }

    public function getOptions(array $defaultOptions = []): array
    {
        $options = parent::getOptions($defaultOptions);
        return [
            'headers' => array_merge($options['headers'] ?? [], $this->getHeaders()),
            'json' => $this->getBody()
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

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function setApiKey(string $apiKey): Collection
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): Collection
    {
        $this->username = $username;
        return $this;
    }

    public function getAuthenticationToken(): string
    {
        return $this->authenticationToken;
    }

    public function setAuthenticationToken(string $authenticationToken): Collection
    {
        $this->authenticationToken = $authenticationToken;
        return $this;
    }

    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }

    public function setAccountNumber(string $accountNumber): Collection
    {
        $this->accountNumber = $accountNumber;
        return $this;
    }

    public function getCollectionDate(): string
    {
        return $this->collectionDate;
    }

    public function setCollectionDate(string $collectionDate): Collection
    {
        $this->collectionDate = $collectionDate;
        return $this;
    }

    public function isClosedForLunch(): bool
    {
        return $this->closedForLunch;
    }

    public function setClosedForLunch(bool $closedForLunch): Collection
    {
        $this->closedForLunch = $closedForLunch;
        return $this;
    }

    public function getEarliestTime(): string
    {
        return $this->earliestTime;
    }

    public function setEarliestTime(string $earliestTime): Collection
    {
        $this->earliestTime = $earliestTime;
        return $this;
    }

    public function getLatestTime(): string
    {
        return $this->latestTime;
    }

    public function setLatestTime(string $latestTime): Collection
    {
        $this->latestTime = $latestTime;
        return $this;
    }

    public function getSpecialInstructions(): string
    {
        return $this->specialInstructions;
    }

    public function setSpecialInstructions(string $specialInstructions): Collection
    {
        $this->specialInstructions = $specialInstructions;
        return $this;
    }
}