<?php
namespace Etsy\Account;

use CG\Etsy\AccessToken;
use CG\Etsy\Client\Factory as ClientFactory;
use CG\Etsy\Request\AccessToken as AccessTokenRequest;
use CG\Etsy\Request\User as UserRequest;
use CG\Etsy\Response\AccessToken as AccessTokenResponse;
use Zend\Session\Container as Session;

class Service
{
    /** @var Session */
    protected $session;
    /** @var ClientFactory */
    protected $clientFactory;

    public function __construct(Session $session, ClientFactory $clientFactory)
    {
        $this->session = $session;
        $this->clientFactory = $clientFactory;
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