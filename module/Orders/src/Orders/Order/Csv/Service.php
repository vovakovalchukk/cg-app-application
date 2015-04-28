<?php
namespace Orders\Order\Csv;

use CG\Account\Client\Service as AccountService;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Entity as Order;
use CG\Stdlib\Log\LogTrait;
use CG\Stdlib\Log\LoggerAwareInterface;
use League\Csv\Writer as CsvWriter;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    const MIME_TYPE = 'text/csv';
    const FILENAME = 'orders.csv';

    protected $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->setAccountService($accountService);
    }

    public function getResponseFromOrderCollection(OrderCollection $orders, $progressKey = null)
    {
        $csvWriter = CsvWriter::createFromFileObject(new \SplTempFileObject(-1));
        $mapper = new Mapper();
        /** @var Order $order */
        $linesAll = [];
        foreach($orders as $key => $order) {
            $linesAll = array_merge($linesAll, $mapper->fromOrderAndItems($order, 'account'));
        }
        $this->logDebugDump($linesAll, "CSV Lines", [], "CSV_DEBUG", []);
        $csvWriter->insertOne($mapper->getOrderAndItemsHeadersNames());
        $csvWriter->insertAll($linesAll);die();
        return new Response(static::MIME_TYPE, static::FILENAME, $csvWriter->__toString());
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