<?php
namespace CG\ShipStation\Command;

use CG\Account\Client\StorageInterface as AccountStorage;
use CG\Account\Shared\Entity as Account;
use CG\ShipStation\Carrier\AccountDecider\Factory as AccountDeciderFactory;
use CG\ShipStation\Client;
use CG\ShipStation\Command\Api\PartnerRequest as PartnerApiRequest;
use CG\ShipStation\Command\Api\Request as ApiRequest;
use CG\ShipStation\Command\Api\Response as ApiResponse;
use CG\ShipStation\RequestAbstract;

class Api
{
    const DEFAULT_METHOD = 'GET';

    /** @var Client */
    protected $client;
    /** @var AccountStorage */
    protected $accountStorage;
    /** @var AccountDeciderFactory */
    protected $accountDeciderFactory;

    public function __construct(Client $client, AccountStorage $accountStorage, AccountDeciderFactory $accountDeciderFactory)
    {
        $this->client = $client;
        $this->accountStorage = $accountStorage;
        $this->accountDeciderFactory = $accountDeciderFactory;
    }

    public function __invoke(int $accountId, string $endpoint, ?string $payload = null, string $method = null): ApiResponse
    {
        $account = $this->fetchShipStationAccount($accountId);
        $request = $this->buildRequest($endpoint, $payload, $method);
        return $this->client->sendRequest($request, $account);
    }

    protected function fetchShipStationAccount(int $accountId): Account
    {
        $account = $this->accountStorage->fetch($accountId);
        // If we've been passed a courier Account convert it to the ShipStation Account
        if ($account->getChannel() != 'shipstationAccount') {
            /** @var AccountDeciderInterface $accountDecider */
            $accountDecider = ($this->accountDeciderFactory)($account->getChannel());
            $account = $accountDecider->getShipStationAccountForRequests($account);
        }
        return $account;
    }

    protected function buildRequest(string $endpoint, ?string $payload = null, string $method = null): RequestAbstract
    {
        if ($this->isPartnerApiEndpoint($endpoint)) {
            return $this->buildPartnerApiRequest($endpoint, $payload, $method);
        }
        return $this->buildApiRequest($endpoint, $payload, $method);
    }

    protected function isPartnerApiEndpoint(string $endpoint): bool
    {
        return (bool)preg_match('|^'.PartnerApiRequest::URI_VERSION_PREFIX.PartnerApiRequest::URI_PREFIX.'|', $endpoint);
    }

    protected function buildApiRequest(string $endpoint, ?string $payload = null, string $method = null): ApiRequest
    {
        return new ApiRequest(
            $endpoint,
            $method ?? static::DEFAULT_METHOD,
            $payload
        );
    }

    protected function buildPartnerApiRequest(string $endpoint, ?string $payload = null, string $method = null): PartnerApiRequest
    {
        return new PartnerApiRequest(
            $endpoint,
            $method ?? static::DEFAULT_METHOD,
            $payload
        );
    }
}