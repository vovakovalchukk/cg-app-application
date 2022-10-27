<?php
namespace Shopify\App;

use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\ActiveUserInterface;
use CG\User\UserInterface;
use CG_Login\Service as LoginService;
use Shopify\Account\Service as AccountService;
use Shopify\App\UserService as AppUserService;
use Zend\Mvc\MvcEvent;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    protected const APP_LINK = 'https://apps.shopify.com/search?q=channelgrabber';

    protected const LOG_CODE = 'ShopifyAppService';
    protected const LOGIN_EXC_MSG = 'Unknown user is trying connect Shopify or use Embedded mode on Shopify. Redirect to login page.';
    protected const EMBEDDED_EXC_MSG = 'User %d is trying to use Embedded mode on Shopify.';
    protected const RECONNECT_MSG = 'User %d is trying to reconnect his Shopify (accountId %d)';

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
        $accountId = null;
        if ($user = $this->activeUser->getActiveUser()) {
            $accountId = $this->userService->getAccountId($user->getId());
        }

        if (!is_null($accountId)) {
            $this->logDebug(static::RECONNECT_MSG, ['userId' => $user->getId(), 'accountId' => $accountId], static::LOG_CODE);
            $this->userService->removeAccountId($user->getId());
        }

        return $this->accountService->getLink($parameters['shop'], $accountId);
    }

    public function isEmbeddedMode(array $parameters): bool
    {
        if (isset($parameters['embedded']) && $parameters['embedded'] == 1) {
            $user = $this->activeUser->getActiveUser();
            $this->logDebug(static::EMBEDDED_EXC_MSG, ['userId' => $user->getId()], static::LOG_CODE);
            return true;
        }

        return false;
    }

    public function getActiveUser(): ?UserInterface
    {
        if (!($user = $this->activeUser->getActiveUser())) {
            $this->logDebug(static::LOGIN_EXC_MSG, [], static::LOG_CODE);
            throw new LoginException('User is not logged in');
        }

        return $user;
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