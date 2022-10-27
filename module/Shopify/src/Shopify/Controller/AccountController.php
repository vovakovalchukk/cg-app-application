<?php
namespace Shopify\Controller;

use CG\Channel\Type as ChannelType;
use Settings\Controller\ChannelController;
use Settings\Module as SettingsModule;
use Shopify\Account\Service as AccountService;
use Shopify\App\LoginException;
use Shopify\App\Service as AppService;
use Zend\Mvc\Controller\AbstractActionController;

class AccountController extends AbstractActionController
{
    const ROUTE_SETUP_LINK = 'Link';
    const ROUTE_SETUP_RETURN = 'Return';

    /** @var AccountService $accountService */
    protected $accountService;
    /** @var AppService */
    protected $appService;

    public function __construct(AccountService $accountService, AppService $appService)
    {
        $this->appService = $appService;
        $this->accountService = $accountService;
    }

    public function setupAction()
    {
        $accountId = $this->params()->fromQuery('accountId');
        $this->appService->cacheUpdateRequest($accountId);
        return $this->plugin('redirect')->toUrl($this->appService->getAppLink());
    }

    public function linkAction()
    {
        $shopHost = $this->params()->fromPost('shopHost');
        $accountId = $this->params()->fromPost('accountId');
        return $this->accountService->getLinkJson($shopHost, $accountId);
    }

    public function returnAction()
    {
        try {
            $this->appService->getActiveUser();
            $account = $this->accountService->activateAccount($this->params()->fromQuery());
            return $this->plugin('redirect')->toUrl(
                $this->getAccountUrl($account->getId())
            );
        } catch (LoginException $exception) {
            $this->redirectToLogin();
        }
    }

    protected function getAccountUrl($accountId = null)
    {
        $route = [SettingsModule::ROUTE, ChannelController::ROUTE, ChannelController::ROUTE_CHANNELS];
        if ($accountId) {
            $route[] = ChannelController::ROUTE_ACCOUNT;
        }

        return $this->plugin('url')->fromRoute(
            implode('/', $route),
            [
                'type' => ChannelType::SALES,
                'account' => $accountId,
            ]
        );
    }

    protected function redirectToLogin(): void
    {
        $mvcEvent = $this->getEvent();
        $this->appService->saveProgressAndRedirectToLogin(
            $mvcEvent,
            $mvcEvent->getRouteMatch()->getMatchedRouteName(),
            $this->params()->fromRoute(),
            [
                'query' => $this->params()->fromQuery()
            ]
        );
    }
}
