<?php
namespace BigCommerce\Account;

use CG\Account\Shared\Entity as Account;
use Zend\Session\Container as SessionContainer;

class Session
{
    /** @var SessionContainer $sessionContainer */
    protected $sessionContainer;

    public function __construct(SessionContainer $sessionContainer)
    {
        $this->setSessionContainer($sessionContainer);
    }

    public function registerAccountId(Account $account)
    {
        $accountId = $account->getId();
        $externalId = $account->getExternalId();
        if ($accountId && $externalId) {
            $this->sessionContainer[$externalId] = $accountId;
        }
    }

    public function getAccountId($externalId)
    {
        if (isset($this->sessionContainer[$externalId])) {
            return $this->sessionContainer[$externalId];
        }
        return $this->sessionContainer[$externalId];
    }

    /**
     * @return self
     */
    protected function setSessionContainer(SessionContainer $sessionContainer)
    {
        $this->sessionContainer = $sessionContainer;
        return $this;
    }
}
