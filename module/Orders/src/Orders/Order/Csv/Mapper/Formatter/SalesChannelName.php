<?php
namespace Orders\Order\Csv\Mapper\Formatter;

use CG\Account\Client\Service as AccountService;
use CG\Order\Shared\Entity as Order;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LogTrait;
use CG\Stdlib\Log\LoggerAwareInterface;
use Orders\Order\Csv\Mapper\FormatterInterface;

class SalesChannelName implements FormatterInterface, LoggerAwareInterface
{
    use LogTrait;

    const LOG_CODE = 'CSV Generation Error';
    const LOG_ACCOUNT_NOT_FOUND = 'Could not find account %s during CSV generation, please investigate';

    protected $accountService;
    protected $cache;

    public function __construct(AccountService $accountService)
    {
        $this->setAccountService($accountService);
        $this->cache = [];
    }

    public function __invoke(Order $order, $fieldName)
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
            $this->logWarning(static::LOG_ACCOUNT_NOT_FOUND, [$accountId], static::LOG_CODE);
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
