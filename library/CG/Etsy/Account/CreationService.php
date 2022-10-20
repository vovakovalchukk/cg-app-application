<?php
namespace CG\Etsy\Account;

use CG\Account\Client\Entity as AccountEntity;
use CG\Account\CreationServiceAbstract;
use CG\Etsy\Credentials;
use CG\Etsy\Response\AccessToken as AccessTokenResponse;
use CG\Etsy\Response\Shop as ShopResponse;

class CreationService extends CreationServiceAbstract
{
    const CHANNEL = 'etsy';

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