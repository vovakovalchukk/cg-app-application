<?php
namespace CG\Etsy;

use CG\Account\Client\Entity as AccountEntity;
use CG\Channel\AccountInterface;
use CG\Etsy\Client\Factory as EtsyClientFactory;
use CG\Etsy\Request\RequestToken as RequestTokenRequest;
use CG\Etsy\Response\RequestToken as RequestTokenResponse;
use CG\Zend\Stdlib\Mvc\Model\Helper\Url as UrlHelper;
use Etsy\Controller\AccountController;
use Zend\Session\Container as Session;

class Account implements AccountInterface
{
    /** @var EtsyClientFactory */
    protected $etsyAccountFactory;
    /** @var UrlHelper */
    protected $urlHelper;
    /** @var Session */
    protected $session;

    public function __construct(EtsyClientFactory $etsyAccountFactory, UrlHelper $urlHelper, Session $session)
    {
        $this->etsyAccountFactory = $etsyAccountFactory;
        $this->urlHelper = $urlHelper;
        $this->session = $session;
    }

    public function getInitialisationUrl(AccountEntity $account, $route, array $routeVariables = [])
    {
        $requestToken = $this->getRequestToken($account->getId());
        $this->session[$requestToken->getToken()] = $requestToken->getSecret();
        return $requestToken->getLoginUrl();
    }

    protected function getRequestToken(?int $accountId): RequestTokenResponse
    {
        $client = $this->etsyAccountFactory->createClientWithoutToken();
        return $client->send(new RequestTokenRequest($this->getCallbackUrl($accountId)));
    }

    protected function getCallbackUrl(?int $accountId): string
    {
        return $this->urlHelper->fromRoute(
            AccountController::ROUTE,
            [],
            [
                'force_canonical' => true,
                'query' => ['account' => $accountId],
            ]
        );
    }
}