<?php
namespace BigCommerce;

use BigCommerce\Account\Session as BigCommerceAccountSession;
use CG\Account\Client\Entity as AccountEntity;
use CG\BigCommerce\Account as BigCommerceAccount;

class Account extends BigCommerceAccount
{
    /** @var BigCommerceAccountSession $accountSession */
    protected $accountSession;

    public function __construct($appUrl, BigCommerceAccountSession $accountSession)
    {
        parent::__construct($appUrl);
        $this->setAccountSession($accountSession);
    }

    public function getInitialisationUrl(AccountEntity $account, $route, $routeParameters = [])
    {
        $this->accountSession->registerAccountId($account);
        return parent::getInitialisationUrl($account, $route, $routeParameters);
    }

    /**
     * @return self
     */
    protected function setAccountSession(BigCommerceAccountSession $accountSession)
    {
        $this->accountSession = $accountSession;
        return $this;
    }
}
