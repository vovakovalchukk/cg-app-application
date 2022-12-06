<?php
namespace CG\Etsy\Account;

use CG\Account\Client\Entity as AccountEntity;
use CG\Account\Client\Mapper as AccountMapper;
use CG\Account\Client\Service as AccountService;
use CG\Account\CreationServiceAbstract;
use CG\Account\Credentials\Cryptor;
use CG\Channel\AccountInterface;
use CG\Etsy\Credentials;
use CG\Etsy\Marketplace\AccountService as MarketplaceAccountService;
use CG\Etsy\Response\AccessToken as AccessTokenResponse;
use CG\Etsy\Response\Shop as ShopResponse;
use CG\Scraper\Client as ScraperClient;

class CreationService extends CreationServiceAbstract
{
    const CHANNEL = 'etsy';

    /** @var MarketplaceAccountService */
    protected $marketplaceAccountService;

    public function __construct(
        AccountService $accountService,
        Cryptor $cryptor,
        AccountMapper $accountMapper,
        ScraperClient $scraperClient,
        MarketplaceAccountService $marketplaceAccountService,
        AccountInterface $channelAccount = null
    ) {
        $this->marketplaceAccountService = $marketplaceAccountService;
        parent::__construct($accountService, $cryptor, $accountMapper, $scraperClient, $channelAccount);
    }

    public function getChannelName()
    {
        return static::CHANNEL;
    }

    public function configureAccount(AccountEntity $account, array $params)
    {
        /** @var AccessTokenResponse $accessTokenResponse */
        $accessTokenResponse = $params['accessTokenResponse'];

        /** @var ShopResponse $shopResponse */
        $shopResponse = $params['shopResponse'];

        $externalData = $account->getExternalData();
        $externalData['marketplace'] = $this->marketplaceAccountService->getMarketplaceMapping($shopResponse);
        $externalData['userId'] = $shopResponse->getUserId();
        $externalData['shopId'] = $shopResponse->getShopId();
        $account->setExternalData($externalData);

        return $account->setCredentials(
            $this->cryptor->encrypt(new Credentials(null, $accessTokenResponse->getRefreshToken()))
        );
    }

    public function getDisplayName(array $params)
    {
        if (!isset($params['shopResponse'])) {
            return static::CHANNEL;
        }

        /** @var ShopResponse $shopResponse */
        $shopResponse = $params['shopResponse'];
        return $shopResponse->getLoginName() ?? static::CHANNEL;
    }
}