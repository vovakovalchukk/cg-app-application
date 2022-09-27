<?php
namespace Shopify\App;

use CG\User\ActiveUserInterface;
use CG_Login\Service as LoginService;
use Shopify\Account\Service as AccountService;
use Zend\Mvc\MvcEvent;

class Service
{
    /** @var AccountService */
    protected $accountService;
    /** @var ActiveUserInterface */
    protected $activeUser;
    /** @var LoginService */
    protected $loginService;

    public function __construct(AccountService $accountService, ActiveUserInterface $activeUser, LoginService $loginService)
    {
        $this->accountService = $accountService;
        $this->activeUser = $activeUser;
        $this->loginService = $loginService;
    }

    public function processOauth($redirectUri, array $parameters): string
    {
        if (!$this->activeUser->getActiveUser()) {
            throw new LoginException('User is not logged in');
        }

        $accountId = $parameters['accountId'] ?? null;

        return $this->accountService->getLink($parameters['shop'], $accountId);
    }

    public function cacheOauthRequest($redirectUri, array $parameters)
    {
        $accountId = $parameters['accountId'] ?? null;
        return $this->accountService->getLink($parameters['shop'], $accountId);
    }

    public function saveProgressAndRedirectToLogin(MvcEvent $event, $route, array $routeParams = [], array $routeOptions = []): void
    {
        $this->loginService->setLandingRoute($route, $routeParams, $routeOptions);
        $this->loginService->loginRedirect($event);
    }
}