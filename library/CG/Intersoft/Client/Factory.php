<?php
namespace CG\Intersoft\Client;

use CG\CourierAdapter\Account as CourierAdapterAccount;
use CG\Intersoft\Client;
use CG\Intersoft\Credentials;
use Zend\Di\Di;

class Factory
{
    /** @var Di */
    protected $di;
    /** @var Client[] */
    protected $accountClients = [];

    public function __construct(Di $di)
    {
        $this->di = $di;
    }

    public function __invoke(CourierAdapterAccount $account): Client
    {
        if ($client = $this->getCachedClient($account)) {
            return $client;
        }

        $client = $this->createNewClient($account);
        $this->cacheClient($client, $account);
        return $client;
    }

    protected function getCachedClient(CourierAdapterAccount $account): ?Client
    {
        if (!isset($this->accountClients[$account->getId()])) {
            return null;
        }
        return $this->accountClients[$account->getId()];
    }

    protected function createNewClient(CourierAdapterAccount $account): Client
    {
        $credentials = Credentials::fromArray($account->getCredentials());
        return $this->di->newInstance('intersoft_live_client', ['account' => $account, 'credentials' => $credentials]);
    }

    protected function cacheClient(Client $client, CourierAdapterAccount $account): void
    {
        $this->accountClients[$account->getId()] = $client;
    }
}