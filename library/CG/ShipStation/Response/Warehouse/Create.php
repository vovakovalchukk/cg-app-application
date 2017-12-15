<?php
namespace CG\ShipStation\Response\Warehouse;

use CG\ShipStation\Messages\Address;
use CG\ShipStation\Messages\Timestamp;
use CG\ShipStation\ResponseAbstract;

class Create extends ResponseAbstract
{
    /** @var  Address */
    protected $address;
    /** @var  Timestamp */
    protected $timestamp;
    /** @var  string */
    protected $warehouseId;

    public function __construct(Address $address, Timestamp $timestamp, string $warehouseId)
    {
        $this->address = $address;
        $this->timestamp = $timestamp;
        $this->warehouseId = $warehouseId;
    }

    protected static function build($decodedJson)
    {
        $address = new Address(
            $decodedJson->name,
            $decodedJson->origin_address->phone,
            $decodedJson->origin_address->address_line1,
            $decodedJson->origin_address->city_locality,
            $decodedJson->origin_address->province,
            $decodedJson->origin_address->postal_code,
            $decodedJson->origin_address->country_code,
            $decodedJson->origin_address->address_line2
        );
        $timestamp = new Timestamp($decodedJson->create_date);
        return new static($address, $timestamp, $decodedJson->warehouse_id);
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
