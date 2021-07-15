<?php
namespace CG\UkMail\Client;

use CG\CourierAdapter\Account as CourierAdapterAccount;
use CG\UkMail\Client as UkMailClient;
use CG\UkMail\Request\RequestInterface;
use CG\UkMail\Request\Rest\RequestInterface as RestRequestInterface;
use CG\UkMail\Request\Soap\RequestInterface as SoapRequestInterface;
use Zend\Di\Di;

class Factory
{
    protected const EXCEPTION_MSG = 'Request %s is implementing incorrect interface';

    /** @var Di */
    protected $di;

    public function __construct(Di $di)
    {
        $this->di = $di;
    }

    public function __invoke(CourierAdapterAccount $account, RequestInterface $request): UkMailClient
    {
        $credentials = $account->getCredentials();

        if ($request instanceof RestRequestInterface) {
            if (isset($credentials['live']) && $credentials['live']) {
                return $this->di->get('ukmail_live_rest_client', ['account' => $account]);
            }
            return $this->di->get('ukmail_test_rest_client', ['account' => $account]);
        }
        if ($request instanceof SoapRequestInterface) {
            if (isset($credentials['live']) && $credentials['live']) {
                return $this->di->get('ukmail_live_soap_client', ['account' => $account]);
            }
            return $this->di->get('ukmail_test_soap_client', ['account' => $account]);
        }

        throw new \RuntimeException(sprintf(static::EXCEPTION_MSG, get_class($request)));
    }
}