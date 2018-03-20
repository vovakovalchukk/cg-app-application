<?php
namespace Etsy\Account;

use CG\Etsy\AccessToken;
use CG\Etsy\Client\Factory as ClientFactory;
use CG\Etsy\Request\AccessToken as AccessTokenRequest;
use CG\Etsy\Request\RequestToken as RequestTokenRequest;
use CG\Etsy\Request\User as UserRequest;
use CG\Etsy\Response\AccessToken as AccessTokenResponse;
use CG\Etsy\Response\RequestToken as RequestTokenResponse;
use CG\Zend\Stdlib\Mvc\Model\Helper\Url as UrlHelper;
use Etsy\Controller\AccountController;
use Zend\Session\Container as Session;

class Service
{
    /** @var Session */
    protected $session;
    /** @var ClientFactory */
    protected $clientFactory;
    /** @var UrlHelper */
    protected $urlHelper;

    public function __construct(Session $session, ClientFactory $clientFactory, UrlHelper $urlHelper)
    {
        $this->session = $session;
        $this->clientFactory = $clientFactory;
        $this->urlHelper = $urlHelper;
    }

    public function getLoginUrl(?int $accountId): string
    {
        $requestToken = $this->getRequestToken($accountId);
        $this->session[$requestToken->getToken()] = $requestToken->getSecret();
        return $requestToken->getLoginUrl();
    }

    protected function getRequestToken(?int $accountId): RequestTokenResponse
    {
        $client = $this->clientFactory->createClientWithoutToken();
        return $client->send(new RequestTokenRequest($this->getCallbackUrl($accountId)));
    }

    protected function getCallbackUrl(?int $accountId): string
    {
        return $this->urlHelper->fromRoute(
            AccountController::ROUTE_REGISTER,
            ['account' => $accountId],
            ['force_canonical' => true]
        );
    }

    public function exchangeRequestTokenForAccessToken(string $token, string $verifier): AccessToken
    {
        $accessToken = $this->getAccessToken(new AccessToken($token, $this->session[$token] ?? ''), $verifier);
        return new AccessToken($accessToken->getToken(), $accessToken->getSecret());
    }

    protected function getAccessToken(AccessToken $accessToken, string $verifier): AccessTokenResponse
    {
        $client = $this->clientFactory->createClientForToken($accessToken);
        return $client->send(new AccessTokenRequest($verifier));
    }

    public function getLoginName(AccessToken $accessToken): string
    {
        $client = $this->clientFactory->createClientForToken($accessToken);
        return $client->send(new UserRequest())->getLoginName();
    }
}