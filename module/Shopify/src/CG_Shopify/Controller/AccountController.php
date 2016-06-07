<?php
namespace CG_Shopify\Controller;

use CG\Channel\Type as ChannelType;
use CG_Shopify\Account\Service as AccountService;
use Settings\Controller\ChannelController;
use Settings\Module as SettingsModule;
use Zend\Mvc\Controller\AbstractActionController;

class AccountController extends AbstractActionController
{
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

    protected function getAccountUrl($accountId = null)
    {
        $route = [SettingsModule::ROUTE, ChannelController::ROUTE, ChannelController::ROUTE_CHANNELS];
        if ($accountId) {
            $route[] = ChannelController::ROUTE_ACCOUNT;
        }

        return $this->plugin('url')->fromRoute(
            implode('/', $route),
            [
                'type' => ChannelType::SHIPPING,
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
