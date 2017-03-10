<?php
namespace Orders\Courier;

use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Collection as AccountCollection;
use CG\Account\Shared\Entity as Account;
use CG\Account\Shared\Filter as AccountFilter;
use CG\Channel\Shipping\Provider\BookingOptions\ActionDescriptionsInterface;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG_UI\View\DataTable;
use Orders\Courier\Service;
use Zend\Di\Di;
use Zend\Di\Exception\ClassNotFoundException;

class SpecificsPage implements LoggerAwareInterface
{
    use LogTrait;

    const OPTION_COLUMN_ALIAS = 'CourierSpecifics%sColumn';
    const LOG_CODE = 'OrderCourierSpecificsPage';
    const LOG_OPTION_COLUMN_NOT_FOUND = 'No column alias called %s found for Account %d, channel %s';

    /** @var Di */
    protected $di;
    /** @var AccountService */
    protected $accountService;
    /** @var Service */
    protected $courierService;

    public function __construct(Di $di, AccountService $accountService, Service $courierService)
    {
        $this->di = $di;
        $this->accountService = $accountService;
        $this->courierService = $courierService;
    }

    /**
     * @return AccountCollection
     */
    public function fetchAccountsById($accountIds)
    {
        $filter = (new AccountFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setId($accountIds);
        return $this->accountService->fetchByFilter($filter);
    }

    public function alterSpecificsTableForSelectedCourier(DataTable $specificsTable, Account $selectedCourier)
    {
        $options = $this->courierService->getCarrierOptions($selectedCourier);
        // We always need the actions column but it must go last
        array_push($options, 'actions');
        foreach ($options as $option) {
            $columnAlias = sprintf(static::OPTION_COLUMN_ALIAS, ucfirst($option));
            try {
                $column = $this->di->get($columnAlias);
                $specificsTable->addColumn($column);
            } catch (ClassNotFoundException $e) {
                $this->logNotice(static::LOG_OPTION_COLUMN_NOT_FOUND, [$columnAlias, $selectedCourier->getId(), $selectedCourier->getChannel()], static::LOG_CODE);
                // No-op, allow for options with no matching column
            }
        }
    }

    /**
     * @return string
     */
    public function getCreateActionDescription(Account $account)
    {
        return $this->getActionDescription('Create', 'Create label', $account);
    }

    /**
     * @return string
     */
    public function getCancelActionDescription(Account $account)
    {
        return $this->getActionDescription('Cancel', 'Cancel', $account);
    }

    /**
     * @return string
     */
    public function getPrintActionDescription(Account $account)
    {
        return $this->getActionDescription('Print', 'Print label', $account);
    }

    /**
     * @return string
     */
    public function getCreateAllActionDescription(Account $account)
    {
        return $this->getActionDescription('CreateAll', 'Create all labels', $account);
    }

    /**
     * @return string
     */
    public function getCancelAllActionDescription(Account $account)
    {
        return $this->getActionDescription('CancelAll', 'Cancel all', $account);
    }

    /**
     * @return string
     */
    public function getPrintAllActionDescription(Account $account)
    {
        return $this->getActionDescription('PrintAll', 'Print all labels', $account);
    }

    protected function getActionDescription($action, $defaultDescription, Account $account)
    {
        $provider = $this->courierService->getCarrierOptionsProvider($account);
        if (!$provider instanceof ActionDescriptionsInterface) {
            return $defaultDescription;
        }
        $method = 'get' . $action . 'ActionDescription';
        return $provider->$method();
    }
}