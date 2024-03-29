<?php
namespace BigCommerce\Controller;

use BigCommerce\App\LoginException;
use BigCommerce\App\Service as BigCommerceAppService;
use BigCommerce\Module;
use CG\Account\Shared\Entity as Account;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG_UI\View\Prototyper\ViewModelFactory;
use Settings\Controller\ChannelController;
use Settings\Module as SettingsModule;
use Zend\Mvc\Controller\AbstractActionController;

class AppController extends AbstractActionController
{
    const ROUTE_OAUTH = 'OAuth';
    const ROUTE_LOAD= 'Load';

    /** @var BigCommerceAppService $appService */
    protected $appService;
    /** @var ViewModelFactory $viewModelFactory */
    protected $viewModelFactory;

    public function __construct(BigCommerceAppService $appService, ViewModelFactory $viewModelFactory)
    {
        $this->setAppService($appService)->setViewModelFactory($viewModelFactory);
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
                $this->appService->cacheOauthRequest($redirectUri, $parameters);
            } catch (\Exception $exception) {
                // Ignore errors and redirect to login
            }
            return $this->redirectToLogin();
        }
    }

    public function loadAction()
    {
        try {
            $account = $this->appService->processLoadRequest($this->params()->fromQuery('signed_payload'), $shopHash);
            return $this->plugin('redirect')->toUrl($this->getAccountUrl($account));
        } catch (NotFound $exception) {
            if (isset($shopHash) && $this->appService->hasCachedOauthRequest($shopHash)) {
                return $this->plugin('redirect')->toUrl($this->getOauthAction($shopHash));
            }
            return $this->getNotFoundView();
        } catch (LoginException $exception) {
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

    protected function getOauthAction($shopHash)
    {
        $route = [Module::ROUTE, AppController::ROUTE_OAUTH];
        return $this->url()->fromRoute(
            implode('/', $route),
            [],
            [
                'query' => ['shopHash' => $shopHash],
            ]
        );
    }

    protected function getNotFoundView()
    {
        $view = $this->viewModelFactory->newInstance(
            [
                'isHeaderBarVisible' => false,
                'isSidebarPresent' => false,
            ]
        );
        return $view->setTemplate('big-commerce/app/notFound');
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
    protected function setAppService(BigCommerceAppService $appService)
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
