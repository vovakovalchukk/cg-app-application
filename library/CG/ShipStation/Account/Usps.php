<?php
namespace CG\ShipStation\Account;

use CG\Account\Client\Entity as AccountClientEntity;
use CG\Account\Credentials\Cryptor;
use CG\Account\Shared\Entity as AccountEntity;
use CG\Channel\AccountInterface;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\ShipStation\Account\Usps\Mapper as AccountMapper;
use CG\ShipStation\Credentials;
use CG\ShipStation\Warehouse\Service as WarehouseService;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;

class Usps implements AccountInterface, LoggerAwareInterface
{
    use LogTrait;

    const LOG_CODE = 'UspsAccountConnector';

    /** @var WarehouseService */
    protected $warehouseService;
    /** @var Cryptor */
    protected $cryptor;
    /** @var OrganisationUnitService */
    protected $organisationUnitService;
    /** @var AccountMapper */
    protected $accountMapper;

    public function __construct(
        WarehouseService $warehouseService,
        Cryptor $cryptor,
        OrganisationUnitService $organisationUnitService,
        AccountMapper $accountMapper
    ) {
        $this->warehouseService = $warehouseService;
        $this->cryptor = $cryptor;
        $this->organisationUnitService = $organisationUnitService;
        $this->accountMapper = $accountMapper;
    }

    public function getInitialisationUrl(AccountClientEntity $account, $route)
    {
        // Not required for USPS
        throw new \BadMethodCallException(__METHOD__ . ' not expected to be called');
    }

    public function connect(AccountEntity $shippingAccount, array $params = []): AccountEntity
    {
        $ou = $this->organisationUnitService->fetch($shippingAccount->getOrganisationUnitId());
        $shipStationAccount = $this->accountMapper->cgShipStationAccountFromShippingAccount($shippingAccount);
        $credentials = $this->cryptor->decrypt($shippingAccount->getCredentials());

        if (!$credentials->has('warehouseId') || $credentials->get('warehouseId') == '') {
            $warehouseId = $this->createWarehouse($shipStationAccount, $ou);
            $this->addWarehouseToAccount($shippingAccount, $credentials, $warehouseId);
        }

        $this->logDebug('Connected USPS account %d for OU %d', ['account' => $shippingAccount->getId(), 'ou' => $ou->getId()], static::LOG_CODE);
        return $shippingAccount;
    }

    protected function createWarehouse(AccountEntity $shipStationAccount, OrganisationUnit $ou): string
    {
        $warehouseResponse = $this->warehouseService->createForOu($ou, $shipStationAccount);
        return $warehouseResponse->getWarehouseId();
    }

    protected function addWarehouseToAccount(
        AccountEntity $shippingAccount,
        Credentials $credentials,
        string $warehouseId
    ): void {
        $credentials->set('warehouseId', $warehouseId);
        $shippingAccount->setCredentials($this->cryptor->encrypt($credentials));
    }
}