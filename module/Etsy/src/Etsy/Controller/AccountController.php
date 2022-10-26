<?php
namespace Etsy\Controller;

use CG\Account\Shared\Entity as Account;
use CG\Etsy\Account\CreationService as AccountCreationService;
use CG\Etsy\Client\State;
use CG\Etsy\Response\AccessToken as AccessTokenResponse;
use CG\Etsy\Response\Shop as ShopResponse;
use CG\User\ActiveUserInterface;
use Etsy\Account\Service as AccountService;
use Settings\Controller\ChannelController;
use Settings\Module as Settings;
use Zend\Mvc\Controller\AbstractActionController;

class AccountController extends AbstractActionController
{
    const ROUTE_INITIALISE = 'EtsyAccountInitialise';
    const ROUTE_REGISTER = 'EtsyAccountRegister';

    /** @var AccountService */
    protected $accountService;
    /** @var ActiveUserInterface */
    protected $activeUserInterface;
    /** @var AccountCreationService */
    protected $accountCreationService;

    public function __construct(
        AccountService $accountService,
        ActiveUserInterface $activeUserInterface,
        AccountCreationService $accountCreationService
    ) {
        $this->accountService = $accountService;
        $this->activeUserInterface = $activeUserInterface;
        $this->accountCreationService = $accountCreationService;
    }

    public function initialiseAction()
    {
        return $this->redirect()->toUrl(
            $this->accountService->getLoginUrl($this->params()->fromRoute('account'))
        );
    }

    public function registerAction()
    {
        $state = $this->params()->fromQuery('state');
        $code = $this->params()->fromQuery('code');

        $codeVerifier = $this->accountService->getCodeVerifier($state);
        $accessTokenResponse = $this->accountService->getAccessToken($code, $codeVerifier);
        $etsyUserId = $this->accountService->getEtsyUserId($accessTokenResponse);
        $etsyShopResponse = $this->accountService->getUsersShop($accessTokenResponse, $etsyUserId);

        $account = $this->connectAccount($accessTokenResponse, $etsyShopResponse, (State::decode($state))->getAccountId());
        return $this->redirect()->toRoute(
            implode('/', [Settings::ROUTE, ChannelController::ROUTE, ChannelController::ROUTE_CHANNELS, ChannelController::ROUTE_ACCOUNT]),
            ['type' => $account->getType(), 'account' => $account->getId()]
        );
    }

    protected function connectAccount(AccessTokenResponse $accessTokenResponse, ShopResponse $shopResponse, ?int $accountId = null): Account
    {
        return $this->accountCreationService->connectAccount(
            $this->activeUserInterface->getCompanyId(),
            $accountId,
            compact('accessTokenResponse', 'shopResponse')
        );
    }
}