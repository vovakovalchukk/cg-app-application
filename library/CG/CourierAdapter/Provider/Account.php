<?php
namespace CG\CourierAdapter\Provider;

use CG\Account\Client\Entity as AccountEntity;
use CG\Channel\AccountInterface;
use CG\CourierAdapater\Account\CredentialRequestInterface;
use CG\CourierAdapater\Account\ThirdPartyAuthInterface;
use CG\CourierAdapter\Provider\Adapter\Service as AdapterService;

class Account implements AccountInterface
{
    const ROUTE = 'Courier Adapter Module';
    const ROUTE_AUTH_SUCCESS = 'Auth Success';
    const ROUTE_AUTH_FAILURE = 'Auth Failure';
    const ROUTE_REQUEST = 'Request Credentials';
    const ROUTE_SETUP = 'Set Up Credentials';

    /** @var AdapterService */
    protected $adapterService;

    public function __construct(AdapterService $adapterService)
    {
        $this->setAdapterService($adapterService);
    }


    public function getInitialisationUrl(AccountEntity $account, $route, array $routeVariables = [])
    {
        $routeVariables['channel'] = $account->getChannel();

        $courierInterface = $this->adapterService->getAdapterCourierInterfaceForAccount($account);
        if ($courierInterface instanceof ThirdPartyAuthInterface) {
            $successUrl = $this->urlHelper->fromRoute(static::ROUTE . '/' . static::ROUTE_AUTH_SUCCESS, $routeVariables);
            $failureUrl = $this->urlHelper->fromRoute(static::ROUTE . '/' . static::ROUTE_AUTH_FAILURE, $routeVariables);
            return $courierInterface->getAuthUrl($successUrl, $failureUrl);

        } elseif ($courierInterface instanceof CredentialRequestInterface) {
            $routeVariables['channel'] = $account->getChannel();
            return $this->urlHelper->fromRoute(static::ROUTE . '/' . static::ROUTE_REQUEST, $routeVariables);
        }

        $routeVariables['channel'] = $account->getChannel();
        return $this->urlHelper->fromRoute(static::ROUTE . '/' . static::ROUTE_SETUP, $routeVariables);
    }

    protected function setAdapterService(AdapterService $adapterService)
    {
        $this->adapterService = $adapterService;
        return $this;
    }
}