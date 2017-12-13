<?php
namespace CG\ShipStation\Request\Warehouse;

use CG\ShipStation\EntityTrait\AddressTrait;
use CG\ShipStation\RequestAbstract;
use CG\ShipStation\Response\Warehouse\Create as Response;

class Create extends RequestAbstract
{
    const METHOD = 'POST';
    const URI = '/warehouses';

    use AddressTrait;

    public function __construct(
        string $name,
        string $phone,
        string $addressLine1,
        string $cityLocality,
        string $postalCode,
        string $countryCode,
        string $addressLine2 = '',
        string $province = ''
    ) {
        $this->name = $name;
        $this->phone = $phone;
        $this->addressLine1 = $addressLine1;
        $this->addressLine2 = $addressLine2;
        $this->cityLocality = $cityLocality;
        $this->province =$province;
        $this->postalCode = $postalCode;
        $this->countryCode = $countryCode;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
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
            'name' => $this->getName(),
            'phone' => $this->getPhone(),
            'company_name' => $this->getName(),
            'address_line1' => $this->getAddressLine1(),
            'address_line2' => $this->getAddressLine2(),
            'city_locality' => $this->getCityLocality(),
            'state_province' => $this->getProvince(),
            'postal_code' => $this->getPostalCode(),
            'country_code' => $this->getCountryCode()
        ];
    }
}
