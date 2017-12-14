<?php
namespace CG\ShipStation;

use CG\Account\Client\Entity as AccountEntity;
use CG\Account\Client\Service as AccountService;
use CG\Account\Credentials\Cryptor;
use CG\Channel\AccountInterface;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\ShipStation\Entity\Address;
use CG\ShipStation\Entity\User as UserRequestEntity;
use CG\ShipStation\Request\Connect\Factory as ConnectFactory;
use CG\ShipStation\Request\Partner\Account as AccountRequest;
use CG\ShipStation\Request\Partner\ApiKey as ApiKeyRequest;
use CG\ShipStation\Request\Shipping\CarrierServices as CarrierServicesRequest;
use CG\ShipStation\Request\Warehouse\Create as CreateWarehouseRequest;
use CG\ShipStation\Response\Connect\Response as ConnectResponse;
use CG\ShipStation\Response\Partner\Account as AccountResponse;
use CG\ShipStation\Response\Partner\ApiKey as ApiKeyResponse;
use CG\ShipStation\Response\Shipping\CarrierServices as CarrierServicesResponse;
use CG\ShipStation\Response\Warehouse\Create as CreateWarehouseResponse;
use CG\ShipStation\ShipStation\Credentials;
use CG\User\Entity as UserEntity;
use CG\User\Service as UserService;

class Account implements AccountInterface
{
    const ROUTE_SETUP = 'Setup';

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

    public function __construct(
        Client $client,
        UserService $userService,
        OrganisationUnitService $ouService,
        AccountService $accountService,
        Cryptor $cryptor,
        ConnectFactory $factory
    ) {
        $this->client = $client;
        $this->userService = $userService;
        $this->ouService = $ouService;
        $this->accountService;
        $this->cryptor = $cryptor;
        $this->connectFactory = $factory;
    }

    public function getInitialisationUrl(AccountEntity $account, $route)
    {
        // TODO: Implement getInitialisationUrl() method.
    }

    public function connect(AccountEntity $account, array $params = []): AccountEntity
    {
        $shipStationAccount = $this->getShipStationAccountForAccount($account);
        /** @var Credentials $credentials */
        $credentials = $this->cryptor->decrypt($shipStationAccount->getCredentials());

        // Create a new Shipstation account and Warehouse if the current Account doesn't have one
        if (!$credentials->getApiKey()) {
            $ou = $this->fetchOrganisationUnit($account->getOrganisationUnitId());
            $user = $this->fetchUser($account->getOrganisationUnitId());

            $accountResponse = $this->sendAccountRequest($ou, $user, $shipStationAccount);
            $apiKeyResponse = $this->sendApiKeyRequest($accountResponse, $shipStationAccount);

            $credentials = (new Credentials())->setApiKey($apiKeyResponse->getEncryptedApiKey());
            $shipStationAccount->setCredentials($this->cryptor->encrypt($credentials));

            if (empty($shipStationAccount->getExternalData()['warehouseId'])) {
                $createWarehouseResponse = $this->sendCreateWarehouseRequest($ou, $shipStationAccount);
                $shipStationAccount->setExternalDataByKey('warehouseId', $createWarehouseResponse->getWarehouseId());
            }
            $shipStationAccount = $this->accountService->save($shipStationAccount);
        }

        $this->createCarrierAccount($account, $shipStationAccount, $params);
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
        $account->setExternalId($connect->getCarrier()->getCarrierId());
        $account->setExternalDataByKey(
            'services',
            json_encode($this->getCarrierServices($connect, $shipStationAccount))
        );
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
        return $this->userService->fetchCollection(1, 1, $ouId)->first();
    }

    protected function fetchOrganisationUnit(int $ouId): OrganisationUnit
    {
        return $this->ouService->fetch($ouId);
    }

    protected function getShipStationAccountForAccount(AccountEntity $account): AccountEntity
    {
        return $this->accountService->fetch($account->getExpiryDate()['shipstationAccountId']);
    }

    protected function getAccountRequest(OrganisationUnit $ou, UserEntity $user): AccountRequest
    {
        $userRequestEntity = new UserRequestEntity(
            $user->getFirstName(),
            $user->getLastName(),
            /** @TODO: TBC if this is the name we want to use or @ou->getAddressFullName() */
            $ou->getAddressCompanyName()
        );
        return (new AccountRequest($userRequestEntity))->setExternalAccountId($ou->getId());
    }

    protected function getApiKeyRequest(AccountResponse $response)
    {
        return new ApiKeyRequest($response->getAccount());
    }

    protected function sendAccountRequest(
        OrganisationUnit $ou,
        UserEntity $user,
        AccountEntity $shipStationAccount
    ): AccountResponse {
        /** @var AccountResponse $response */
        $response = $this->client->sendRequest($this->getAccountRequest($ou, $user), $shipStationAccount);
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

    protected function sendCreateWarehouseRequest(
        OrganisationUnit $ou,
        AccountEntity $shipStationAccount
    ): CreateWarehouseResponse {
        $address = new Address(
            $ou->getAddressFullName(),
            $ou->getPhoneNumber(),
            $ou->getAddress1(),
            $ou->getAddressCity(),
            /** @TODO: check if our country code matches the one required by ShipStation */
            $ou->getAddressPostcode(),
            $ou->getAddressCountryCode(),
            $ou->getAddress2(),
            $ou->getEmailAddress()
        );
        $request = new CreateWarehouseRequest($address);
        /** @var CreateWarehouseResponse $response */
        $response = $this->client->sendRequest($request, $shipStationAccount);
        return $response;
    }
}
