<?php
namespace CG\Walmart\Account;

use CG\Account\Client\Entity as AccountEntity;
use CG\Account\Client\Mapper as AccountMapper;
use CG\Account\Client\Service as AccountService;
use CG\Account\CreationServiceAbstract;
use CG\Account\Credentials\Cryptor;
use CG\Channel\AccountInterface;
use CG\Scraper\Client as ScraperClient;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Walmart\Client\Factory as ClientFactory;
use CG\Walmart\Credentials;

class CreationService extends CreationServiceAbstract
{
    const CHANNEL = 'walmart';

    /** @var ClientFactory */
    protected $clientFactory;

    public function __construct(
        AccountService $accountService,
        Cryptor $cryptor,
        AccountMapper $accountMapper,
        ScraperClient $scraperClient,
        ClientFactory $clientFactory,
        AccountInterface $channelAccount = null
    ) {
        parent::__construct($accountService, $cryptor, $accountMapper, $scraperClient, $channelAccount);
        $this->clientFactory = $clientFactory;
    }

    public function getChannelName()
    {
        return static::CHANNEL;
    }

    public function getDisplayName(array $params)
    {
        return 'Walmart';
    }

    public function configureAccount(AccountEntity $account, array $params)
    {
        $account->setCredentials(
            $this->cryptor->encrypt(new Credentials($params['clientId'], $params['clientSecret']))
        );
        $this->testAccount($account);
        $account->setExternalDataByKey('fulfillmentLagTime', $params['fulfillmentLagTime']);
        return $account;
    }

    protected function testAccount(AccountEntity $account): void
    {
        try {
            // Requesting a Client will request a token which will test the connection
            ($this->clientFactory)($account);
        } catch (StorageException $e) {
            throw new \InvalidArgumentException('There was a problem connecting up that account. Please check your credentials and try again', $e->getCode(), $e);
        }
    }
}