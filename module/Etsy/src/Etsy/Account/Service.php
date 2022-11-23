<?php
namespace Etsy\Account;

use CG\Etsy\Client\AccessToken;
use CG\Etsy\Client\Factory as ClientFactory;
use CG\Etsy\Client\Scopes;
use CG\Etsy\Client\State;
use CG\Etsy\Request\AuthorizationCode;
use CG\Etsy\Request\UserShops;
use CG\Etsy\Response\AccessToken as AccessTokenResponse;
use CG\Zend\Stdlib\Mvc\Model\Helper\Url as UrlHelper;
use Etsy\Controller\AccountController;
use Zend\Session\Container as Session;
use CG\Etsy\Client\AccessToken\Service as AccessTokenService;
use CG\Etsy\Response\Shop as EtsyShop;

class Service
{
    protected const URI = 'https://www.etsy.com/oauth/connect?response_type=code&redirect_uri=%s&scope=%s&client_id=%s&state=%s&code_challenge=%s&code_challenge_method=S256';

    /** @var Session */
    protected $session;
    /** @var ClientFactory */
    protected $clientFactory;
    /** @var UrlHelper */
    protected $urlHelper;
    /** @var AccessTokenService */
    protected $accessTokenService;

    public function __construct(
        Session $session,
        ClientFactory $clientFactory,
        UrlHelper $urlHelper,
        AccessTokenService $accessTokenService
    ) {
        $this->session = $session;
        $this->clientFactory = $clientFactory;
        $this->urlHelper = $urlHelper;
        $this->accessTokenService = $accessTokenService;
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
            $this->getCallbackUrl(),
            implode(' ', Scopes::getAllScopes()),
            $this->clientFactory->getClientId(),
            $state,
            $codeChallenge
        );
    }

    public function getCodeVerifier($encodedState): string
    {
        $state = State::decode($encodedState);
        return $this->session[$state->getId()];
    }

    public function getAccessToken(string $code, string $codeVerifier, ?int $accountId = null): AccessTokenResponse
    {
        $authorizationCodeRequest = new AuthorizationCode(
            $this->clientFactory->getClientId(),
            $this->getCallbackUrl(),
            $code,
            $codeVerifier
        );
        $client = $this->clientFactory->createClientWithoutToken();
        /** @var AccessTokenResponse $accessTokenResponse */
        return $client->send($authorizationCodeRequest);
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

    protected function getCallbackUrl(): string
    {
        return $this->urlHelper->fromRoute(
            AccountController::ROUTE_REGISTER,
            [],
            ['force_canonical' => true]
        );
    }

    public function getEtsyUserId(AccessTokenResponse $accessTokenResponse): int
    {
        [$userId, ] = explode('.', $accessTokenResponse->getRefreshToken());
        return $userId;
    }

    public function getUsersShop(AccessTokenResponse $accessTokenResponse, int $userId): EtsyShop
    {
        $client = $this->clientFactory->createClientWithAccessToken(
            new AccessToken($accessTokenResponse->getAccessToken(), $accessTokenResponse->getExpiresIn())
        );

        return $client->send(new UserShops($userId));
    }
}