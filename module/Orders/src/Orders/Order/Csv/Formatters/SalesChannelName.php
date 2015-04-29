<?php
namespace Orders\Order\Csv\Formatters;

use CG\Account\Client\Service as AccountService;
use CG\Order\Shared\Entity as Order;
use CG\Stdlib\Exception\Runtime\NotFound;
use Orders\Order\Csv\FormatterInterface;

class SalesChannelName implements FormatterInterface
{
    protected $accountService;
    protected $cache;

    public function __construct(AccountService $accountService)
    {
        $this->setAccountService($accountService);
        $this->cache = [];
    }

    public function __invoke(Order $order)
    {
        $accountName = $this->fetchAccountDisplayName($order->getAccountId());
        if($order->getItems()->count() === 0) {
            return [$accountName];
        }

        $column = [];
        for($i = 0; $i < $order->getItems()->count(); $i++) {
            $column[] = $accountName;
        }

        return $column;
    }

    protected function fetchAccountDisplayName($accountId)
    {
        if(isset($this->cache[$accountId])) {
            return $this->cache[$accountId];
        }

        try {
            $account = $this->getAccountService()->fetch($accountId);
            $this->cache[$accountId] = $account->getDisplayName();
            return $account->getDisplayName();
        } catch (NotFound $e) {
            return '';
        }
    }

    /**
     * @return AccountService
     */
    protected function getAccountService()
    {
        return $this->accountService;
    }

    /**
     * @param AccountService $accountService
     * @return $this
     */
    public function setAccountService(AccountService $accountService)
    {
        $this->accountService = $accountService;
        return $this;
    }
}
