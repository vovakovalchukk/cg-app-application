<?php
namespace CG\ShipStation;

use CG\Account\Client\Entity as AccountEntity;
use CG\Account\Client\Mapper as AccountMapper;
use CG\Account\Client\Service as AccountService;
use CG\Account\Credentials\Cryptor;
use CG\Account\Shared\Filter as AccountFilter;
use CG\Channel\AccountInterface;
use CG\Channel\Type;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\ShipStation\Carrier\Service as CarrierService;
use CG\ShipStation\Messages\User as UserRequestEntity;
use CG\ShipStation\Request\Connect\Factory as ConnectFactory;
use CG\ShipStation\Request\Partner\Account as AccountRequest;
use CG\ShipStation\Request\Partner\ApiKey as ApiKeyRequest;
use CG\ShipStation\Request\Partner\GetAccountByExternalId as GetAccountByExternalIdRequest;
use CG\ShipStation\Request\Shipping\CarrierServices as CarrierServicesRequest;
use CG\ShipStation\Response\Connect\Response as ConnectResponse;
use CG\ShipStation\Response\Partner\Account as AccountResponse;
use CG\ShipStation\Response\Partner\ApiKey as ApiKeyResponse;
use CG\ShipStation\Response\Shipping\CarrierServices as CarrierServicesResponse;
use CG\ShipStation\ShipStation\Credentials;
use CG\ShipStation\Warehouse\Service as WarehouseService;
use CG\Stdlib\DateTime;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\Entity as UserEntity;
use CG\User\Service as UserService;
use CG\Zend\Stdlib\Mvc\Model\Helper\Url as UrlHelper;
use Guzzle\Http\Exception\ClientErrorResponseException;

class Account implements AccountInterface, LoggerAwareInterface
{
    use LogTrait;

    const KEY_SHIPSTATION_ACCOUNT_ID = 'shipstationAccountId';
    const ROUTE = 'ShipStation Module';
    const ROUTE_SETUP = 'Setup';

    const LOG_CODE = 'ShipStation Account Creation';
    const LOG_MESSAGE_EXISTING_SHIPSTATION_ACCOUNT = 'Using the existing ShipStation account with ID "%s"';
    const LOG_MESSAGE_ACCOUNT_CREATED = 'Successfully created a ShipStation account for OU with ID "%s"';
    const LOG_MESSAGE_API_KEY_GENERATED = 'Successfully generated a new API Key for user with ShipStation account ID "%s"';
    const LOG_MESSAGE_ACCOUNT_SAVED = 'Successfully created a new ShipStation account with ID "%s" for OU "%s"';
    const LOG_MESSAGE_CARRIER_ACCOUNT_CONNECTED = 'Successfully connected a new "%s" account with ID "%s" for OU "%s"';
    const LOG_MESSAGE_SERVICES_SAVED = 'Successfully fetched and saved the shipping services for ShipStation account ID "%s';

    /** @var Client  */
    protected $client;
    /** @var  UserService */
    protected $userService;
    /** @var  OrganisationUnitService */
    protected $ouService;
    /** @var  AccountService */
    protected $accountService;
    /** @var  Cryptor */
    protected $cryptor;
    /** @var  ConnectFactory */
    protected $connectFactory;
    /** @var  AccountMapper */
    protected $accountMapper;
    /** @var UrlHelper */
    protected $urlHelper;
    /** @var WarehouseService */
    protected $warehouseService;
    /** @var CarrierService */
    protected $carrierService;

    public function __construct(
        Client $client,
        UserService $userService,
        OrganisationUnitService $ouService,
        AccountService $accountService,
        Cryptor $cryptor,
        ConnectFactory $factory,
        AccountMapper $accountMapper,
        UrlHelper $urlHelper,
        WarehouseService $warehouseService,
        CarrierService $carrierService
    ) {
        $this->client = $client;
        $this->userService = $userService;
        $this->ouService = $ouService;
        $this->accountService = $accountService;
        $this->cryptor = $cryptor;
        $this->connectFactory = $factory;
        $this->accountMapper = $accountMapper;
        $this->urlHelper = $urlHelper;
        $this->warehouseService = $warehouseService;
        $this->carrierService = $carrierService;
    }

    public function getInitialisationUrl(AccountEntity $account, $route)
    {
        $routeVariables = ['channel' => $account->getChannel()];
        return $this->urlHelper->fromRoute(static::ROUTE . '/' . static::ROUTE_SETUP, $routeVariables);
    }

    public function connect(AccountEntity $account, array $params = []): AccountEntity
    {
        $ou = $this->fetchOrganisationUnit($account->getOrganisationUnitId());

        try {
            $shipStationAccount = $this->fetchExistingShipStationAccount($account);
            $this->logDebug(static::LOG_MESSAGE_EXISTING_SHIPSTATION_ACCOUNT, [$shipStationAccount->getId()], static::LOG_CODE, ['shipstationAccountId' => $shipStationAccount->getId()]);
        } catch (NotFound $e) {
            $user = $this->fetchUser($account->getOrganisationUnitId());
            $accountResponse = $this->createAccountOnShipStation($ou, $user, $account);
            $this->logDebug(static::LOG_MESSAGE_ACCOUNT_CREATED, [$user->getOrganisationUnitId()], static::LOG_CODE, ['organisationUnitId' => $user->getOrganisationUnitId()]);
            $apiKeyResponse = $this->sendApiKeyRequest($accountResponse, $account);
            $this->logDebug(static::LOG_MESSAGE_API_KEY_GENERATED, [$accountResponse->getAccount()->getAccountId()], static::LOG_CODE, ['shipStationAccountId' => $accountResponse->getAccount()->getAccountId(), 'apiKey' => $apiKeyResponse->getEncryptedApiKey()]);
            $shipStationAccount = $this->createShipStationAccount($account, $apiKeyResponse->getEncryptedApiKey());
            $this->createWarehouse($shipStationAccount, $ou);
            $this->saveShipStationAccount($shipStationAccount);
        }

        $this->createCarrierAccount($account, $shipStationAccount, $params);
        $account->setExternalDataByKey(static::KEY_SHIPSTATION_ACCOUNT_ID, $shipStationAccount->getId());
        return $account;
    }

    public function createCarrierAccount(
        AccountEntity $account,
        AccountEntity $shipStationAccount,
        array $params = []
    ): void {
        if ($account->getExternalId()) {
            throw new \RuntimeException('Cannot update an existing ShipStation Carrier account.');
        }
        $connect = $this->connectCarrierToShipStation($account, $shipStationAccount, $params);
        $this->logDebug(static::LOG_MESSAGE_CARRIER_ACCOUNT_CONNECTED, [$account->getChannel(), $connect->getCarrier()->getCarrierId(), $account->getOrganisationUnitId()]);
        $account->setExternalId($connect->getCarrier()->getCarrierId());

        $carrier = $this->carrierService->getCarrierForAccount($account);
        if ($carrier->isActivationDelayed()) {
            return;
        }
        $account->setExternalDataByKey(
            'services',
            $this->getCarrierServices($connect, $shipStationAccount)->getJsonResponse()
        );
        $this->logDebug(static::LOG_MESSAGE_SERVICES_SAVED, [$shipStationAccount->getExternalId()]);
    }

    protected function createAccountOnShipStation(
        OrganisationUnit $ou,
        UserEntity $user,
        AccountEntity $account
    ): AccountResponse {
        try {
            return $this->sendAccountRequest($ou, $user, $account);
        } catch (StorageException $e) {
            if ($e->getPrevious() instanceof ClientErrorResponseException) {
                return $this->fetchExistingAccountFromShipStation($ou, $account);
            }
            throw $e;
        }
    }

    protected function fetchExistingAccountFromShipStation(OrganisationUnit $ou, AccountEntity $account)
    {
        $existingAccountRequest = GetAccountByExternalIdRequest::buildFromExternalAccountId($ou->getId());
        /** @var AccountResponse $response */
        $response = $this->client->sendRequest($existingAccountRequest, $account);
        return $response;
    }

    protected function saveShipStationAccount(AccountEntity $account): void
    {
        $this->accountService->save($account);
        $this->logDebug(static::LOG_MESSAGE_ACCOUNT_SAVED, [$account->getOrganisationUnitId(), $account->getId()], static::LOG_CODE, ['organisationUnitId' => $account->getOrganisationUnitId(), 'accountId' => $account->getId()]);
    }

    protected function createWarehouse(AccountEntity $account, OrganisationUnit $ou): void
    {
        if (!empty($account->getExternalData()['warehouseId'])) {
            return;
        }
        $createWarehouseResponse = $this->warehouseService->createForOu($ou, $account);
        $account->setExternalDataByKey('warehouseId', $createWarehouseResponse->getWarehouseId());
    }

    protected function connectCarrierToShipStation(
        AccountEntity $account,
        AccountEntity $shipStationAccount,
        array $params = []
    ): ConnectResponse {
        return $this->client->sendRequest(
            $request = $this->connectFactory->buildRequestForAccount($account, $params),
            $shipStationAccount
        );
    }

    protected function getCarrierServices(
        ConnectResponse $connect,
        AccountEntity $shipStationAccount
    ): CarrierServicesResponse {
        return $this->client->sendRequest(
            new CarrierServicesRequest($connect->getCarrier()),
            $shipStationAccount
        );
    }

    protected function fetchUser(int $ouId): UserEntity
    {
        return $this->userService->fetchCollection(1, 1, $ouId)->getFirst();
    }

    protected function fetchOrganisationUnit(int $ouId): OrganisationUnit
    {
        return $this->ouService->fetch($ouId);
    }

    protected function fetchExistingShipStationAccount(AccountEntity $account): AccountEntity
    {
        try {
            if (!isset($account->getExternalData()[static::KEY_SHIPSTATION_ACCOUNT_ID])) {
                throw new NotFound();
            }
            return $this->accountService->fetch($account->getExternalData()['shipstationAccountId']);
        } catch (NotFound $e) {
            $filter = new AccountFilter(
                1,
                1,
                [],
                [$account->getOrganisationUnitId()],
                ['shipstationAccount']
            );
            return $this->accountService->fetchByFilter($filter)->getFirst();
        }
    }

    protected function createShipStationAccount(AccountEntity $account, string $apiKey): AccountEntity
    {
        return $this->accountMapper->fromArray([
            'channel' => 'shipstationAccount',
            'organisationUnitId' => $account->getOrganisationUnitId(),
            'displayName' => 'ShipStation',
            'credentials' => $this->cryptor->encrypt(new Credentials($apiKey)),
            'active' => true,
            'deleted' => false,
            'pending' => false,
            'cgCreationDate' => (new DateTime())->format(DateTime::FORMAT),
            'type' => [Type::SHIPPING_PROVIDER]
        ]);
    }

    protected function getAccountRequest(OrganisationUnit $ou, UserEntity $user): AccountRequest
    {
        $userRequestEntity = new UserRequestEntity(
            $user->getFirstName(),
            $user->getLastName(),
            $ou->getAddressCompanyName()
        );
        return new AccountRequest($userRequestEntity, $ou->getId(), $ou->getAddressCountryCode());
    }

    protected function getApiKeyRequest(AccountResponse $response)
    {
        return new ApiKeyRequest($response->getAccount());
    }

    protected function sendAccountRequest(
        OrganisationUnit $ou,
        UserEntity $user,
        AccountEntity $account
    ): AccountResponse {
        /** @var AccountResponse $response */
        $response = $this->client->sendRequest($this->getAccountRequest($ou, $user), $account);
        return $response;
    }

    protected function sendApiKeyRequest(
        AccountResponse $accountResponse,
        AccountEntity $shipStationAccount
    ): ApiKeyResponse {
        /** @var ApiKeyResponse $response */
        $response = $this->client->sendRequest($this->getApiKeyRequest($accountResponse), $shipStationAccount);
        return $response;
    }
}
