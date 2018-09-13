<?php
namespace CG\ShipStation\Warehouse;

use CG\Account\Shared\Entity as Account;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\ShipStation\Client;
use CG\ShipStation\Messages\Address;
use CG\ShipStation\Request\Warehouse\Create as CreateWarehouseRequest;
use CG\ShipStation\Response\Warehouse\Create as CreateWarehouseResponse;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    const LOG_CODE = 'ShipStationWarehouseService';

    /** @var Client */
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function createForOu(
        OrganisationUnit $ou,
        Account $shipStationAccount
    ): CreateWarehouseResponse {
        $address = $this->sanitiseAddress(Address::fromOrganisationUnit($ou));
        $request = new CreateWarehouseRequest($address);
        $response = $this->client->sendRequest($request, $shipStationAccount);
        $this->logDebug('Successfully created a new warehouse with ID %s for OU %d', [$response->getWarehouseId(), $ou->getId()], static::LOG_CODE);
        return $response;
    }

    protected function sanitiseAddress(Address $address): Address
    {
        // Warehouses must have a province (county) set
        if (!$address->getProvince()) {
            $address->setProvince($this->determineProvinceForAddress($address));
        }
        return $address;
    }

    protected function determineProvinceForAddress(Address $address): string
    {
        return $address->getCityLocality() ?: $address->getAddressLine2() ?: '';
    }
}