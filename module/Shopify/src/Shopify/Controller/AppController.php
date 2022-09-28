<?php
namespace Shopify\Controller;

use CG\Account\Shared\Entity as Account;
use CG\Channel\Type as ChannelType;
use CG_UI\View\Prototyper\ViewModelFactory;
use Settings\Controller\ChannelController;
use Settings\Module as SettingsModule;
use Shopify\Account\Service as AccountService;
use Shopify\App\EmbeddedException;
use Zend\Mvc\Controller\AbstractActionController;
use Shopify\App\LoginException;
use Shopify\App\Service as AppService;

class AppController extends AbstractActionController
{
    const ROUTE_OAUTH = 'OAuth';
    const ROUTE_SETUP_RETURN = 'Return';

    /** @var AppService */
    protected $appService;
    /** @var AccountService */
    protected $accountService;
    /** @var ViewModelFactory */
    protected $viewModelFactory;

    public function __construct(AppService $appService, AccountService $accountService, ViewModelFactory $viewModelFactory)
    {
        $this->appService = $appService;
        $this->accountService = $accountService;
        $this->viewModelFactory = $viewModelFactory;
    }

    public function oauthAction()
    {
        $redirectUri = $this->url()->fromRoute(null, $this->params()->fromRoute(), ['force_canonical' => true]);
        $parameters = $this->params()->fromQuery();

//        $shopHost = $this->params()->fromQuery('shop');
//        $accountId = $this->params()->fromQuery('accountId');



        try {
            $link = $this->appService->processOauth($redirectUri, $parameters);
            return $this->plugin('redirect')->toUrl($link);
//            return $this->plugin('redirect')->toUrl($this->getAccountUrl($account));
        } catch (LoginException $exception) {
//            try {
//                $this->appService->cacheOauthRequest($redirectUri, $parameters);
//            } catch (\Exception $exception) {
                // Ignore errors and redirect to login
//            }
            $this->redirectToLogin();
        } catch (EmbeddedException $exception) {
            return $this->plugin('redirect')->toUrl($this->getAccountUrl());
        }
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

    protected function getAccountUrl(?int $accountId = null)
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
}