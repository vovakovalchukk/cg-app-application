<?php
namespace CG\RoyalMailApi\Response\Manifest;

use CG\RoyalMailApi\Response\FromJsonInterface;
use stdClass;

class Shipment implements FromJsonInterface
{
    /** @var string */
    protected $code;
    /** @var string */
    protected $shipmentNumber;

    public function __construct(string $code, string $shipmentNumber)
    {
        $this->code = $code;
        $this->shipmentNumber = $shipmentNumber;
    }

    public static function fromJson(stdClass $json)
    {
        return new static(
            (string) $json->code,
            (string) $json->shipmentNumber
        );
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getShipmentNumber(): string
    {
        return $this->shipmentNumber;
    }
}
