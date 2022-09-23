<?php
namespace Shopify\Controller;

use CG\Account\Shared\Entity as Account;
use CG\Channel\Type as ChannelType;
use CG_UI\View\Prototyper\ViewModelFactory;
use Settings\Controller\ChannelController;
use Settings\Module as SettingsModule;
use Shopify\Account\Service as AccountService;
use Zend\Mvc\Controller\AbstractActionController;
use Shopify\App\LoginException;
use Shopify\App\Service as AppService;

class AppController extends AbstractActionController
{
    const ROUTE_OAUTH = 'OAuth';
    const ROUTE_SETUP_RETURN = 'Return';

    /** @var AppService */
    protected $appService;
    /** @var ViewModelFactory */
    protected $viewModelFactory;

    public function __construct(AppService $appService, ViewModelFactory $viewModelFactory)
    {
        $this->appService = $appService;
        $this->viewModelFactory = $viewModelFactory;
    }

    public function oauthAction()
    {
        $redirectUri = $this->url()->fromRoute(null, $this->params()->fromRoute(), ['force_canonical' => true]);
        $parameters = $this->params()->fromQuery();

        try {
            $account = $this->appService->processOauth($redirectUri, $parameters);
            return $this->plugin('redirect')->toUrl($this->getAccountUrl($account));
        } catch (LoginException $exception) {
            try {
//                $this->appService->cacheOauthRequest($redirectUri, $parameters);
            } catch (\Exception $exception) {
                // Ignore errors and redirect to login
            }
            return $this->redirectToLogin();
        }
    }

    protected function getAccountUrl(Account $account)
    {
        $route = [SettingsModule::ROUTE, ChannelController::ROUTE, ChannelController::ROUTE_CHANNELS, ChannelController::ROUTE_ACCOUNT];
        return $this->url()->fromRoute(
            implode('/', $route),
            [
                'type' => $account->getType(),
                'account' => $account->getId(),
            ]
        );
    }

    protected function redirectToLogin()
    {
        $mvcEvent = $this->getEvent();
        return $this->appService->saveProgressAndRedirectToLogin(
            $mvcEvent,
            $mvcEvent->getRouteMatch()->getMatchedRouteName(),
            $this->params()->fromRoute(),
            [
                'query' => $this->params()->fromQuery()
            ]
        );
    }

    /**
     * @return self
     */
    protected function setAppService(AppService $appService)
    {
        $this->appService = $appService;
        return $this;
    }

    /**
     * @return self
     */
    protected function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }
}