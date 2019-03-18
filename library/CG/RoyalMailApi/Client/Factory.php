<?php
namespace CG\RoyalMailApi\Client;

use CG\CourierAdapter\Account as CourierAdapterAccount;
use CG\RoyalMailApi\Client;
use CG\RoyalMailApi\Client\AuthToken\Storage as AuthTokenStorage;
use CG\RoyalMailApi\Credentials;
use CG\RoyalMailApi\Request\AuthToken as AuthTokenRequest;
use CG\RoyalMailApi\Response\AuthToken as AuthTokenResponse;
use DateTime;
use Zend\Di\Di;

class Factory
{
    /** @var Di */
    protected $di;
    /** @var AuthTokenStorage */
    protected $authTokenStorage;

    /** @var Client[] */
    protected $accountClients = [];

    public function __construct(Di $di, AuthTokenStorage $authTokenStorage)
    {
        $this->di = $di;
        $this->authTokenStorage = $authTokenStorage;
    }

    public function __invoke(CourierAdapterAccount $account)
    {
        if ($client = $this->getCachedClient($account)) {
            return $client;
        }

        $client = $this->createNewClient($account);
        $authToken = $this->getAuthToken($client, $account);
        $client->setAuthToken($authToken);
        $this->cacheClient($client, $account);
        return $client;
    }

    protected function getCachedClient(CourierAdapterAccount $account): ?Client
    {
        if (!isset($this->accountClients[$account->getId()])) {
            return null;
        }
        $client = $this->accountClients[$account->getId()];
        if ($this->isAuthTokenExpired($client->getAuthToken())) {
            $this->removeCachedClient($account);
            return null;
        }
        return $client;
    }

    protected function isAuthTokenExpired(?AuthToken $authToken): bool
    {
        return (!$authToken || $authToken->getExpires() <= (new DateTime()));
    }

    protected function removeCachedClient(CourierAdapterAccount $account): void
    {
        unset($this->accountClients[$account->getId()]);
    }

    protected function createNewClient(CourierAdapterAccount $account): Client
    {
        $credentials = Credentials::fromArray($account->getCredentials());
        return $this->di->newInstance('royalmailapi_live_client', ['account' => $account, 'credentials' => $credentials]);
    }

    protected function getAuthToken(Client $client, CourierAdapterAccount $account): AuthToken
    {
        $authToken = $this->authTokenStorage->fetchForAccount($account);
        if ($authToken) {
            return $authToken;
        }
        $authToken = $this->requestAuthToken($client);
        $this->authTokenStorage->saveForAccount($authToken, $account);
        return $authToken;
    }

    protected function requestAuthToken(Client $client): AuthToken
    {
        $request = new AuthTokenRequest();
        /** @var AuthTokenResponse $response */
        $response = $client->send($request);
        return $response->getAuthToken();
    }

    protected function cacheClient(Client $client, CourierAdapterAccount $account): void
    {
        $this->accountClients[$account->getId()] = $client;
    }
}