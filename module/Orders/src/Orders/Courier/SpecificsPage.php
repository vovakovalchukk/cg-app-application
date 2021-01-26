<?php
namespace Orders\Courier;

use CG\Account\Shared\Collection as AccountCollection;
use CG\Account\Shared\Entity as Account;
use CG\Account\Shipping\Service as AccountService;
use CG\Channel\Shipping\Provider\BookingOptions;
use CG\Locale\Length as LocaleLength;
use CG\Locale\Mass as LocaleMass;
use CG\OrganisationUnit\Collection as OrganisationUnits;
use CG\OrganisationUnit\Filter as OrganisationUnitFilter;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\ActiveUserInterface;
use CG_UI\View\DataTable;
use CG_UI\View\DataTable\Column;
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
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var OrganisationUnitService */
    protected $organisationUnitService;

    protected $bookOptionInterfaces = [
        'Create' => BookingOptions\CreateActionDescriptionInterface::class,
        'Export' => BookingOptions\ExportActionDescriptionInterface::class,
        'Cancel' => BookingOptions\CancelActionDescriptionInterface::class,
        'Print' => BookingOptions\PrintActionDescriptionInterface::class,
        'Dispatch' => BookingOptions\DispatchActionDescriptionInterface::class,
        'FetchRates' => BookingOptions\FetchRatesActionDescriptionInterface::class,
        'CreateAll' => BookingOptions\CreateAllActionDescriptionInterface::class,
        'ExportAll' => BookingOptions\ExportAllActionDescriptionInterface::class,
        'CancelAll' => BookingOptions\CancelAllActionDescriptionInterface::class,
        'PrintAll' => BookingOptions\PrintAllActionDescriptionInterface::class,
        'DispatchAll' => BookingOptions\DispatchAllActionDescriptionInterface::class,
        'FetchAllRates' => BookingOptions\FetchAllRatesActionDescriptionInterface::class,
    ];

    protected $columnModifiers = [
        'weight' => 'alterWeightColumnUnit',
        'width'  => 'alterDimensionColumnUnit',
        'height' => 'alterDimensionColumnUnit',
        'length' => 'alterDimensionColumnUnit',
    ];

    public function __construct(
        Di $di,
        AccountService $accountService,
        Service $courierService,
        ActiveUserInterface $activeUserContainer,
        OrganisationUnitService $organisationUnitService
    ) {
        $this->di = $di;
        $this->accountService = $accountService;
        $this->courierService = $courierService;
        $this->activeUserContainer = $activeUserContainer;
        $this->organisationUnitService = $organisationUnitService;
    }

    public function fetchAccountsById($accountIds): AccountCollection
    {
        return $this->accountService->fetchShippingAccounts($accountIds);
    }

    public function fetchOrganisationUnitsForAccounts(AccountCollection $accounts): OrganisationUnits
    {
        return $this->organisationUnitService->fetchCollectionByFilter(
            (new OrganisationUnitFilter('all', 1))
                ->setId(
                    array_merge(
                        $accounts->getArrayOf('organisationUnitId'),
                        $accounts->getArrayOf('rootOrganisationUnitId')
                    )
                )
        );
    }

    public function alterSpecificsTableForSelectedCourier(DataTable $specificsTable, Account $selectedCourier)
    {
        $options = $this->courierService->getCarrierOptions($selectedCourier);
        // We always need the actions column but it must go last
        array_push($options, 'actions');
        foreach ($options as $option) {
            $this->addOptionalColumnToTable($option, $specificsTable, $selectedCourier);
        }
    }

    protected function addOptionalColumnToTable(string $option, DataTable $specificsTable, Account $selectedCourier): void
    {
        $columnAlias = sprintf(static::OPTION_COLUMN_ALIAS, ucfirst($option));
        try {
            $column = $this->di->get($columnAlias);
            $column = $this->alterColumnForSelectedCourier($column, $selectedCourier);
            $specificsTable->addColumn($column);
        } catch (ClassNotFoundException $e) {
            $this->logNotice(static::LOG_OPTION_COLUMN_NOT_FOUND,
                [$columnAlias, $selectedCourier->getId(), $selectedCourier->getChannel()], static::LOG_CODE);
            // No-op, allow for options with no matching column
        }
    }

    protected function alterColumnForSelectedCourier(Column $column, Account $selectedCourier): Column
    {
        if (!isset($this->columnModifiers[$column->getColumn()])) {
            return $column;
        }
        $method = $this->columnModifiers[$column->getColumn()];
        return $this->{$method}($column, $selectedCourier);
    }

    protected function alterWeightColumnUnit(Column $column): Column
    {
        $unit = LocaleMass::getForLocale($this->activeUserContainer->getLocale());
        return $this->alterUnitOfMeasureColumn($column, $unit);
    }

    protected function alterDimensionColumnUnit(Column $column): Column
    {
        $unit = LocaleLength::getForLocale($this->activeUserContainer->getLocale());
        return $this->alterUnitOfMeasureColumn($column, $unit);
    }

    protected function alterUnitOfMeasureColumn(Column $column, string $unit): Column
    {
        $value = $column->getViewModel()->getVariable('value');
        $alteredValue = str_replace('{unit}', $unit, $value);
        $column->getViewModel()->setVariable('value', $alteredValue);
        return $column;
    }

    public function getCreateActionDescription(Account $account): string
    {
        return $this->getActionDescription('Create', 'Create label', $account);
    }

    public function getExportActionDescription(Account $account): string
    {
        return $this->getActionDescription('Export', 'Download file', $account);
    }

    public function getCancelActionDescription(Account $account): string
    {
        return $this->getActionDescription('Cancel', 'Cancel', $account);
    }

    public function getPrintActionDescription(Account $account): string
    {
        return $this->getActionDescription('Print', 'Print label', $account);
    }

    public function getDispatchActionDescription(Account $account): string
    {
        return $this->getActionDescription('Dispatch', 'Dispatch order', $account);
    }

    public function getFetchRatesActionDescription(Account $account): string
    {
        return $this->getActionDescription('FetchRates', 'Fetch rates', $account);
    }

    public function getCreateAllActionDescription(Account $account): string
    {
        return $this->getActionDescription('CreateAll', 'Create all labels', $account);
    }

    public function getExportAllActionDescription(Account $account): string
    {
        return $this->getActionDescription('ExportAll', 'Download file for all', $account);
    }

    public function getCancelAllActionDescription(Account $account): string
    {
        return $this->getActionDescription('CancelAll', 'Cancel all', $account);
    }

    public function getPrintAllActionDescription(Account $account): string
    {
        return $this->getActionDescription('PrintAll', 'Print all labels', $account);
    }

    public function getDispatchAllActionDescription(Account $account): string
    {
        return $this->getActionDescription('DispatchAll', 'Dispatch all orders', $account);
    }

    public function getFetchAllRatesActionDescription(Account $account): string
    {
        return $this->getActionDescription('FetchAllRates', 'Fetch all rates', $account);
    }

    protected function getActionDescription(string $action, string $defaultDescription, Account $account): string
    {
        $provider = $this->courierService->getCarrierOptionsProvider($account);
        if (!($provider instanceof $this->bookOptionInterfaces[$action] ?? '')) {
            return $defaultDescription;
        }
        return $provider->{'get' . $action . 'ActionDescription'}($account);
    }
}