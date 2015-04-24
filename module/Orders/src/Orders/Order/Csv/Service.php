<?php
namespace Orders\Order\Csv;

use CG\Account\Client\Service as AccountService;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Entity as Order;

class Service
{
    const MIME_TYPE = 'text/csv';
    const FILENAME = 'orders.csv';

    protected $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->setAccountService($accountService);
    }

    public function getResponseFromOrderCollection(OrderCollection $orders, $progressKey = null)
    {
        /** @var Order $order */
        foreach($orders as $order) {
            if($order->getItems() == null || $order->getItems()->count() == 0) {

            }
        }
    }

    public function addLine(array $line)
    {

    }

    protected function fetchAccountName($accountId)
    {
        $account = $this->getAccountService()->fetch($accountId);
        return $account->getDisplayName();
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