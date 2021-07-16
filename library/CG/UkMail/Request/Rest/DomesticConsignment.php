<?php
namespace CG\UkMail\Request\Rest;

use CG\UkMail\Request\AbstractPostRequest;
use CG\UkMail\Response\Rest\DomesticConsignment as Response;

class DomesticConsignment extends AbstractPostRequest implements RequestInterface
{
    protected const URI = 'gateway/DomesticConsignment/1.0/DomesticConsignment';

    /** @var string */
    protected $apiKey;
    /** @var string */
    protected $username;
    /** @var string */
    protected $authenticationToken;
    /** @var string */
    protected $accountNumber;
    /** @var string */
    protected $collectionJobNumber;

    //@todo address for delivery and recipient
    /** @var Address[] */
    protected $deliveryDetails;
    /** @var string */
    protected $serviceKey;
    /** @var int */
    protected $items;
    /** @var int */
    protected $totalWeight;
    /** @var string */
    protected $customerReference;
    /** @var string */
    protected $alternativeReference;

    //@todo parcel object
    /** @var Parcel[] */
    protected $parcels;
    /** @var int */
    protected $extendedCoverUnits;
    /** @var bool */
    protected $exchangeOnDelivery;
    /** @var bool */
    protected $bookin;
    /** @var bool */
    protected $inBoxReturn;
    //@todo not required
    /** @var InBoxReturnDetail */
    protected $inboxReturnDetail;
    /** @var string */
    protected $labelFormat;

    public function __construct(
        string $apiKey,
        string $username,
        string $authenticationToken,
        string $accountNumber,
        string $collectionJobNumber
    ) {
        $this->apiKey = $apiKey;
        $this->username = $username;
        $this->authenticationToken = $authenticationToken;
        $this->accountNumber = $accountNumber;
        $this->collectionJobNumber = $collectionJobNumber;
    }

    protected function getBody(): array
    {
        return [
            'userName' => $this->getUsername(),
            'authenticationToken' => $this->getAuthenticationToken(),
            'accountNumber' => $this->getAccountNumber(),
            'collectionInfo' => [
                'collectionJobNumber' => '',
            ],
            'delivery' => '', //address->toArray()
            'serviceKey' => '',
            'items' => '',
            'totalWeight' => '', //weight in whole Kg
            'customerReference' => '',
//            'alternativeReference' => null
            'parcels' => '', //parcels->toArray();
//            'extendedCoverUnits' => 0,
            'recipient' => '', //address->toArray()
//            'exchangeOnDelivery' => '',
//            'bookin' => '',
//            'inBoxReturn' => '',
//            'inboxReturnDetail' => '',
            'labelFormat' => '',
        ];
    }

    public function getResponseClass(): string
    {
        // TODO: Implement getResponseClass() method.
    }

    protected function getHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'x-api-key' => $this->apiKey
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
}