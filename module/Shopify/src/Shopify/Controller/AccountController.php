<?php
namespace Shopify\Controller;

use CG\Channel\Type as ChannelType;
use Settings\Controller\ChannelController;
use Settings\Module as SettingsModule;
use Shopify\Account\Service as AccountService;
use Zend\Mvc\Controller\AbstractActionController;

class AccountController extends AbstractActionController
{
    const ROUTE_SETUP_LINK = 'Link';
    const ROUTE_SETUP_RETURN = 'Return';

    /** @var AccountService $accountService */
    protected $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->setAccountService($accountService);
    }

    public function setupAction()
    {
        $accountId = $this->params()->fromQuery('accountId');
        return $this->accountService->getSetupView($accountId, $this->getAccountUrl($accountId));
    }

    public function linkAction()
    {
        $shopHost = $this->params()->fromPost('shopHost');
        $accountId = $this->params()->fromPost('accountId');
        return $this->accountService->getLinkJson($shopHost, $accountId);
    }

    public function returnAction()
    {
        $account = $this->accountService->activateAccount($this->params()->fromQuery());
        return $this->plugin('redirect')->toUrl(
            $this->getAccountUrl($account->getId())
        );
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

    /**
     * @return self
     */
    protected function setAccountService(AccountService $accountService)
    {
        $this->accountService = $accountService;
        return $this;
    }
} 