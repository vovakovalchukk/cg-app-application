<?php
namespace CG\CourierExport;

use CG\Account\Client\Entity as AccountEntity;
use CG\Account\Client\Mapper as AccountMapper;
use CG\Account\Client\Service as AccountService;
use CG\Account\CreationServiceAbstract;
use CG\Account\Credentials\Cryptor;
use CG\Channel\Type;
use CG\Scraper\Client as ScraperClient;

class CreationService extends CreationServiceAbstract
{
    /** @var string */
    protected $channel;
    /** @var string */
    protected $channelName;

    public function __construct(
        string $channel,
        string $channelName,
        AccountService $accountService,
        Cryptor $cryptor,
        AccountMapper $accountMapper,
        ScraperClient $scraperClient,
        Account $channelAccount
    ) {
        parent::__construct($accountService, $cryptor, $accountMapper, $scraperClient, $channelAccount);
        $this->channel = $channel;
        $this->channelName = $channelName;
    }

    public function getChannelName()
    {
        return $this->channel;
    }

    public function configureAccount(AccountEntity $account, array $params)
    {
        $account->setType([Type::SHIPPING]);
        $account->setCredentials($this->cryptor->encrypt($this->getCredentials()));
        return $account;
    }

    protected function getCredentials(): Credentials
    {
        return new Credentials();
    }

    public function getDisplayName(array $params)
    {
        return $this->channelName;
    }
}