<?php
namespace Etsy\Account;

use CG\Etsy\AccessToken;
use CG\Etsy\Client\Factory as ClientFactory;
use CG\Etsy\Client\Scopes;
use CG\Etsy\Client\State;
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
    protected const URI = 'https://www.etsy.com/oauth/connect?response_type=code&redirect_uri=%s&scope=%s&client_id=%s&state=%s&code_challenge=%s&code_challenge_method=S256';

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
        $codeVerifier = $this->generateCodeVerifier();
        $codeChallenge = $this->generateCodeChallenge($codeVerifier);
        $id = uniqid('etsy');
        $state = (new State($id, $accountId))->encode();

        $this->session[$id] = $codeVerifier;

        return sprintf(
            static::URI,
            $this->getCallbackUrl($accountId),
            implode(' ', Scopes::getAllScopes()),
            $this->clientFactory->getClientId(),
            $state,
            $codeChallenge
        );
    }

    protected function generateCodeVerifier(): string
    {
        $verifierBytes = random_bytes(64);
        return rtrim(strtr(base64_encode($verifierBytes), "+/", "-_"), "=");
    }

    protected function generateCodeChallenge(string $codeVerifier): string
    {
        $challengeBytes = hash("sha256", $codeVerifier, true);
        return rtrim(strtr(base64_encode($challengeBytes), "+/", "-_"), "=");
    }

    /** @deprecated */
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