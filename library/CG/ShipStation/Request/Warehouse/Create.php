<?php
namespace CG\ShipStation\Request\Warehouse;

use CG\ShipStation\Messages\Address;
use CG\ShipStation\RequestAbstract;
use CG\ShipStation\Response\Warehouse\Create as Response;

class Create extends RequestAbstract
{
    const METHOD = 'POST';
    const URI = '/warehouses';

    /** @var  Address */
    protected $address;

    public function __construct(Address $address)
    {
        $this->address = $address;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->address->getName(),
            'origin_address' => $this->buildAddress(),
            'return_address' => $this->buildAddress()
        ];
    }

    public function getResponseClass(): string
    {
        return Response::class;
    }

    protected function buildAddress(): array
    {
        return [
            'name' => $this->address->getName(),
            'phone' => $this->address->getPhone(),
            'company_name' => $this->address->getName(),
            'address_line1' => $this->address->getAddressLine1(),
            'address_line2' => $this->address->getAddressLine2(),
            'city_locality' => $this->address->getCityLocality(),
            'state_province' => $this->address->getProvince(),
            'postal_code' => $this->address->getPostalCode(),
            'country_code' => $this->address->getCountryCode()
        ];
    }
}
