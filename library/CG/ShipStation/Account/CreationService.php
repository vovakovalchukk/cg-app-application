<?php
namespace CG\ShipStation\Account;

use CG\Account\Client\Entity as AccountEntity;
use CG\Account\Client\Mapper as AccountMapper;
use CG\Account\Client\Service as AccountService;
use CG\Account\CreationServiceAbstract;
use CG\Account\Credentials\Cryptor;
use CG\Channel\AccountInterface;
use CG\Channel\Type as ChannelType;
use CG\Scraper\Client as ScraperClient;
use CG\ShipStation\Account;
use CG\ShipStation\Carrier\AccountDecider\Factory as AccountDeciderFactory;
use CG\ShipStation\Credentials;
use CG\ShipStation\Carrier\Service as CarrierService;
use ShipStation\Webhook\Service as WebhookService;

/**
 * Class CreationService
 * @package CG\ShipStation\Account
 * @method Cryptor getCryptor()
 * @method Account getChannelAccount()
 */
class CreationService extends CreationServiceAbstract
{
    /** @var  CarrierService */
    protected $carrierService;
    /** @var AccountDeciderFactory */
    protected $accountDeciderFactory;
    /** @var WebhookService */
    protected $webhookService;

    public function __construct(
        AccountService $accountService,
        Cryptor $cryptor,
        AccountMapper $accountMapper,
        ScraperClient $scraperClient,
        CarrierService $carrierService,
        AccountDeciderFactory $accountDeciderFactory,
        WebhookService $webhookService,
        AccountInterface $channelAccount = null
    ) {
        parent::__construct($accountService, $cryptor, $accountMapper, $scraperClient, $channelAccount);
        $this->carrierService = $carrierService;
        $this->accountDeciderFactory = $accountDeciderFactory;
        $this->webhookService = $webhookService;
    }

    public function configureAccount(AccountEntity $account, array $params)
    {
        $this->setAccountChannel($account, $params);
        $carrier = $this->carrierService->getCarrierForAccount($account);
        $account->setType([ChannelType::SHIPPING])
            ->setDisplayName($carrier->getDisplayName())
            ->setCredentials($this->getCredentialsFromParams($params))
            ->setPending($carrier->isActivationDelayed());

        return $this->getChannelAccount()->connect($account, $params);
    }

    protected function setAccountChannel(AccountEntity $account, array $params)
    {
        if (!isset($params['channel'])) {
            throw new \RuntimeException('The required parameter `channel` is missing from given params array');
        }
        $account->setChannel($params['channel']);
    }

    protected function getCredentialsFromParams(array $params): string
    {
        $credentials = new Credentials();
        foreach ($params as $field => $value) {
            $credentials->set($field, $value);
        }
        return $this->getCryptor()->encrypt($credentials);
    }

    protected function afterAccountSave(AccountEntity $account)
    {
        $carrier = $this->carrierService->getCarrierForAccount($account);
        if ($carrier->isActivationDelayed()) {
            $this->registerForCarrierConnectedWebhook($account);
        }
        return $account;
    }

    protected function registerForCarrierConnectedWebhook(AccountEntity $account): void
    {
        $accountDecider = ($this->accountDeciderFactory)($account->getChannel());
        $shipStationAccount = $accountDecider->getShipStationAccountForRequests($account);
        $this->webhookService->registerForCarrierConnectedWithActiveUser($account, $shipStationAccount);
    }

    /**
     * @return string
     * The channel name is handled by @configureAccount method
     */
    public function getChannelName()
    {
        return '';
    }

    /**
     * @param array $params
     * @return string
     * The channel display name is handled by @configureAccount method
     */
    public function getDisplayName(array $params)
    {
        return '';
    }
}
