<?php
namespace Shopify\App;

use CG\User\ActiveUserInterface;
use CG_Login\Service as LoginService;
use Shopify\Account\Service as AccountService;
use Shopify\App\UserService as AppUserService;
use Zend\Mvc\MvcEvent;

class Service
{
    protected const APP_LINK = 'https://apps.shopify.com/search?q=channelgrabber';

    /** @var AccountService */
    protected $accountService;
    /** @var ActiveUserInterface */
    protected $activeUser;
    /** @var LoginService */
    protected $loginService;
    /** @var AppUserService */
    protected $userService;

    public function __construct(
        AccountService $accountService,
        ActiveUserInterface $activeUser,
        LoginService $loginService,
        AppUserService $userService
    ) {
        $this->accountService = $accountService;
        $this->activeUser = $activeUser;
        $this->loginService = $loginService;
        $this->userService = $userService;
    }

    public function processOauth($redirectUri, array $parameters): string
    {
        if (!($user = $this->activeUser->getActiveUser())) {
            throw new LoginException('User is not logged in');
        }

        $accountId = $this->userService->getAccountId($user->getId());
        if (!is_null($accountId)) {
            $this->userService->removeAccountId($user->getId());
        }

        return $this->accountService->getLink($parameters['shop'], $accountId);
    }

    public function saveProgressAndRedirectToLogin(MvcEvent $event, $route, array $routeParams = [], array $routeOptions = []): void
    {
        $this->loginService->setLandingRoute($route, $routeParams, $routeOptions);
        $this->loginService->loginRedirect($event);
    }

    public function getAppLink(): string
    {
        return static::APP_LINK;
    }

    public function cacheUpdateRequest(?int $accountId = null): void
    {
        if (is_null($accountId)) {
            return;
        }
        $user = $this->activeUser->getActiveUser();
        $this->userService->saveAccountId($user->getId(), $accountId);
    }
}