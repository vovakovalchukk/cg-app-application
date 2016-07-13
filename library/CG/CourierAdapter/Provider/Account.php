<?php
namespace CG\CourierAdapter\Provider;

use CG\Account\Client\Entity as AccountEntity;
use CG\Channel\AccountInterface;
use CG\CourierAdapter\Account\CredentialRequestInterface;
use CG\CourierAdapter\Account\ThirdPartyAuthInterface;
use CG\CourierAdapter\CourierInterface;
use CG\CourierAdapter\Provider\Adapter\Service as AdapterService;
use CG_UI\View\Helper\RemoteUrl as RemoteUrlHelper;
use CG\Zend\Stdlib\Mvc\Model\Helper\Url as UrlHelper;

class Account implements AccountInterface
{
    const ROUTE = 'Courier Adapter Module';
    const ROUTE_AUTH_SUCCESS = 'Auth Success';
    const ROUTE_AUTH_FAILURE = 'Auth Failure';
    const ROUTE_REQUEST = 'Request Credentials';
    const ROUTE_SETUP = 'Set Up Credentials';

    /** @var AdapterService */
    protected $adapterService;
    /** @var UrlHelper */
    protected $urlHelper;
    /** @var RemoteUrlHelper */
    protected $remoteUrlHelper;

    public function __construct(AdapterService $adapterService, UrlHelper $urlHelper, RemoteUrlHelper $remoteUrlHelper)
    {
        $this->setAdapterService($adapterService)
            ->setUrlHelper($urlHelper)
            ->setRemoteUrlHelper($remoteUrlHelper);
    }


    public function getInitialisationUrl(AccountEntity $account, $route, array $routeVariables = [])
    {
        $routeVariables['channel'] = $account->getChannel();

        $courierInterface = $this->adapterService->getAdapterCourierInterfaceForAccount($account);
        if ($courierInterface instanceof CredentialRequestInterface && !$account->getId()) {
            return $this->urlHelper->fromRoute(static::ROUTE . '/' . static::ROUTE_REQUEST, $routeVariables);
        }
        if ($courierInterface instanceof ThirdPartyAuthInterface) {
            return $this->getInitialisationUrlForThirdPartyAuth($account, $routeVariables, $courierInterface);
        }

        $url = $this->urlHelper->fromRoute(static::ROUTE . '/' . static::ROUTE_SETUP, $routeVariables);
        if ($account->getId()) {
            $url .= '?accountId=' . $account->getId();
        }
        return $url;
    }

    protected function getInitialisationUrlForThirdPartyAuth(
        AccountEntity $account,
        array $routeVariables,
        CourierInterface $courierInterface
    ) {
        $successUri = $this->urlHelper->fromRoute(static::ROUTE . '/' . static::ROUTE_AUTH_SUCCESS, $routeVariables);
        $failureUri = $this->urlHelper->fromRoute(static::ROUTE . '/' . static::ROUTE_AUTH_FAILURE, $routeVariables);
        if ($account->getId()) {
            $successUri .= '?accountId=' . $account->getId();
            $failureUri .= '?accountId=' . $account->getId();
        }

        $successUrl = $this->remoteUrlHelper->__invoke($successUri, 'app');
        $failureUrl = $this->remoteUrlHelper->__invoke($failureUri, 'app');
        $authUrl = $courierInterface->getAuthUrl($successUrl, $failureUrl);

        if (!preg_match('/^http(s)?:\/\//i', $authUrl)) {
            $authUrl = 'http://' . $authUrl;
        }
        return $authUrl;
    }

    protected function setAdapterService(AdapterService $adapterService)
    {
        $this->adapterService = $adapterService;
        return $this;
    }

    protected function setUrlHelper(UrlHelper $urlHelper)
    {
        $this->urlHelper = $urlHelper;
        return $this;
    }

    protected function setRemoteUrlHelper(RemoteUrlHelper $remoteUrlHelper)
    {
        $this->remoteUrlHelper = $remoteUrlHelper;
        return $this;
    }
}