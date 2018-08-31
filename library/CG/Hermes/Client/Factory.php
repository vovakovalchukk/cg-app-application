<?php
namespace CG\Hermes\Client;

use CG\CourierAdapter\Account as CourierAdapterAccount;
use Zend\Di\Di;

class Factory
{
    /** @var Di */
    protected $di;

    public function __construct(Di $di)
    {
        $this->di = $di;
    }

    public function __invoke(CourierAdapterAccount $account)
    {
        $credentials = $account->getCredentials();
        if (isset($credentials['liveCredentials']) && $credentials['liveCredentials']) {
            return $this->di->get('hermes_live_client', ['account' => $account]);
        }
        return $this->di->get('hermes_test_client', ['account' => $account]);
    }
}