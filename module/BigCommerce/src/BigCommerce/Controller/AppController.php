<?php
namespace BigCommerce\Controller;

use BigCommerce\App\LoginException;
use BigCommerce\App\Service as BigCommerceAppService;
use CG\Account\Shared\Entity as Account;
use CG_Login\Event\LandingUrlEvent;
use CG_Login\Event\LoginEvent;
use Settings\Controller\ChannelController;
use Settings\Module as SettingsModule;
use Zend\Mvc\Controller\AbstractActionController;

class AppController extends AbstractActionController
{
    const ROUTE_OAUTH = 'OAuth';
    const ROUTE_LOAD= 'Load';

    /** @var BigCommerceAppService $appService */
    protected $appService;

    public function __construct(BigCommerceAppService $appService)
    {
        $this->setAppService($appService);
    }

    public function oauthAction()
    {
        $account = $this->appService->processOauth(
            $this->url()->fromRoute(null, $this->params()->fromRoute(), ['force_canonical' => true]),
            $this->params()->fromQuery()
        );
        return $this->plugin('redirect')->toUrl($this->getAccountUrl($account));
    }

    public function loadAction()
    {
        try {
            $account = $this->appService->processLoadRequest($this->params()->fromQuery('signed_payload'));
            return $this->plugin('redirect')->toUrl($this->getAccountUrl($account));
        } catch (LoginException $exception) {
            $mvcEvent = $this->getEvent();
            $routeMatch = $mvcEvent->getRouteMatch();
            LandingUrlEvent::triggerSet($routeMatch->getMatchedRouteName(), $routeMatch->getParams());
            return LoginEvent::triggerRedirect($mvcEvent);
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

    /**
     * @return self
     */
    protected function setAppService(BigCommerceAppService $appService)
    {
        $this->appService = $appService;
        return $this;
    }
}
