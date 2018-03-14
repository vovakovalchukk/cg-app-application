<?php
namespace CG\Etsy;

use CG\Account\Client\Entity as AccountEntity;
use CG\Channel\AccountInterface;
use CG\Etsy\Client\Factory as EtsyClientFactory;
use CG\Etsy\Request\GetRequestToken;

class Account implements AccountInterface
{
    /** @var EtsyClientFactory */
    protected $etsyAccountFactory;

    public function __construct(EtsyClientFactory $etsyAccountFactory)
    {
        $this->etsyAccountFactory = $etsyAccountFactory;
    }

    public function getInitialisationUrl(AccountEntity $account, $route, array $routeVariables = [])
    {
        $client = $this->etsyAccountFactory->createClientWithoutToken();
        $response = $client->send(new GetRequestToken([]));
        return $response->getLoginUrl();
    }
}