<?php
namespace Orders\Order\Csv\Formatters;

use CG\Account\Client\Service as AccountService;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Stdlib\Exception\Runtime\NotFound;
use Orders\Order\Csv\FormatterInterface;

class SalesChannelName implements FormatterInterface
{
    protected $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->setAccountService($accountService);
    }

    public function __invoke(OrderCollection $orders)
    {
        $column = [];
        foreach($orders as $order) {
            $accountName = $this->fetchAccountDisplayName($order->getAccountId());
            if($order->getItems()->count() === 0) {
                $column[] = $accountName;
                continue;
            }

            for($i = 0; $i < $order->getItems()->count(); $i++) {
                $column[] = $accountName;
            }
        }
        return $column;
    }

    protected function fetchAccountDisplayName($accountId)
    {
        try {
            $account = $this->getAccountService()->fetch($accountId);
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
