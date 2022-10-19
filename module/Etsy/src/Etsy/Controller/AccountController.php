<?php
namespace Etsy\Controller;

use CG\Account\Shared\Entity as Account;
use CG\Etsy\AccessToken;
use CG\Etsy\Account\CreationService as AccountCreationService;
use CG\Etsy\Request\AuthorizationCode;
use CG\User\ActiveUserInterface;
use Etsy\Account\Service as AccountService;
use Settings\Controller\ChannelController;
use Settings\Module as Settings;
use Zend\Mvc\Controller\AbstractActionController;
use CG\Etsy\Response\AccessToken as AccessTokenResponse;

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

        $account = $this->connectAccount($accessTokenResponse);
        return $this->redirect()->toRoute(
            implode('/', [Settings::ROUTE, ChannelController::ROUTE, ChannelController::ROUTE_CHANNELS, ChannelController::ROUTE_ACCOUNT]),
            ['type' => $account->getType(), 'account' => $account->getId()]
        );
    }

    protected function getAccessToken(): AccessToken
    {


        return $this->accountService->exchangeRequestTokenForAccessToken(
            $this->params()->fromQuery('oauth_token', ''),
            $this->params()->fromQuery('oauth_verifier', '')
        );
    }

    protected function getLoginName(AccessToken $accessToken): string
    {
        return $this->accountService->getLoginName($accessToken);
    }

    protected function connectAccount(AccessTokenResponse $accessToken): Account
    {
        return $this->accountCreationService->connectAccount(
            $this->activeUserInterface->getCompanyId(),
            $this->params()->fromRoute('account'),
            compact('accessToken')
        );
    }
}