<?php
namespace CG\ShipStation\Response\Warehouse;

use CG\ShipStation\EntityTrait\AddressTrait;
use CG\ShipStation\EntityTrait\TimestampableTrait;
use CG\ShipStation\ResponseAbstract;

class Create extends ResponseAbstract
{
    use AddressTrait;
    use TimestampableTrait;

    /** @var  string */
    protected $warehouseId;

    protected function build($decodedJson)
    {
        return $this->setCreatedAt($decodedJson->create_date)
            ->setName($decodedJson->name)
            ->setWarehouseId($decodedJson->warehouse_id)
            ->setPhone($decodedJson->origin_address->phone)
            ->setAddressLine1($decodedJson->origin_address->address_line1)
            ->setAddressLine2($decodedJson->origin_address->address_line2)
            ->setCityLocality($decodedJson->origin_address->city_locality)
            ->setProvince($decodedJson->origin_address->province)
            ->setPostalCode($decodedJson->origin_address->postal_code)
            ->setCountryCode($decodedJson->origin_address->country_code);
    }

    public function getWarehouseId(): string
    {
        return $this->warehouseId;
    }

    public function setWarehouseId(string $warehouseId)
    {
        $this->warehouseId = $warehouseId;
        return $this;
    }
}
