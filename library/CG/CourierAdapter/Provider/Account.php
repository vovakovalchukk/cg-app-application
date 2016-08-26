<?php
namespace CG\CourierAdapter\Provider;

use CG\Account\Client\Entity as AccountEntity;
use CG\Channel\AccountInterface;
use CG\CourierAdapter\Account\CredentialRequestInterface;
use CG\CourierAdapter\Account\ThirdPartyAuthInterface;
use CG\CourierAdapter\CourierInterface;
use CG\CourierAdapter\Provider\Account\CreationService as AccountCreationService;
use CG\CourierAdapter\Provider\Implementation\Service as AdapterImplementationService;
use CG_UI\View\Helper\RemoteUrl as RemoteUrlHelper;
use CG\Zend\Stdlib\Mvc\Model\Helper\Url as UrlHelper;

class Account implements AccountInterface
{
    const ROUTE = 'Courier Adapter Module';
    const ROUTE_AUTH_SUCCESS = 'Auth Success';
    const ROUTE_AUTH_FAILURE = 'Auth Failure';
    const ROUTE_REQUEST = 'Request Credentials';
    const ROUTE_SETUP = 'Set Up Credentials';

    /** @var AdapterImplementationService */
    protected $adapterImplementationService;
    /** @var UrlHelper */
    protected $urlHelper;
    /** @var RemoteUrlHelper */
    protected $remoteUrlHelper;

    public function __construct(AdapterImplementationService $adapterImplementationService, UrlHelper $urlHelper, RemoteUrlHelper $remoteUrlHelper)
    {
        $this->setAdapterImplementationService($adapterImplementationService)
            ->setUrlHelper($urlHelper)
            ->setRemoteUrlHelper($remoteUrlHelper);
    }

    public function getInitialisationUrl(AccountEntity $account, $route, array $routeVariables = [])
    {
        $routeVariables['channel'] = $account->getChannel();
        $courierInstance = $this->adapterImplementationService->getAdapterImplementationCourierInstanceForAccount($account);

        if ($courierInstance instanceof CredentialRequestInterface
            && !$account->getId()
            && !isset($routeVariables[AccountCreationService::REQUEST_CREDENTIALS_SKIPPED_FIELD])
        ) {
            return $this->urlHelper->fromRoute(static::ROUTE . '/' . static::ROUTE_REQUEST, $routeVariables);
        }
        unset($routeVariables[AccountCreationService::REQUEST_CREDENTIALS_SKIPPED_FIELD]);

        if ($courierInstance instanceof ThirdPartyAuthInterface) {
            return $this->getInitialisationUrlForThirdPartyAuth($account, $routeVariables, $courierInstance);
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
        CourierInterface $courierInstance
    ) {
        $successUri = $this->urlHelper->fromRoute(static::ROUTE . '/' . static::ROUTE_AUTH_SUCCESS, $routeVariables);
        $failureUri = $this->urlHelper->fromRoute(static::ROUTE . '/' . static::ROUTE_AUTH_FAILURE, $routeVariables);
        if ($account->getId()) {
            $successUri .= '?accountId=' . $account->getId();
            $failureUri .= '?accountId=' . $account->getId();
        }

        $successUrl = $this->remoteUrlHelper->__invoke($successUri, 'app');
        $failureUrl = $this->remoteUrlHelper->__invoke($failureUri, 'app');
        $authUrl = $courierInstance->getAuthUrl($successUrl, $failureUrl);

        if (!preg_match('/^http(s)?:\/\//i', $authUrl)) {
            $authUrl = 'http://' . $authUrl;
        }
        return $authUrl;
    }

    protected function setAdapterImplementationService(AdapterImplementationService $adapterImplementationService)
    {
        $this->adapterImplementationService = $adapterImplementationService;
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