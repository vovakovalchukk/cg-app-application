<?php
namespace CG\RoyalMailApi\Response\Shipment;

use CG\RoyalMailApi\ResponseInterface;
use CG\RoyalMailApi\Response\FromJsonInterface;
use stdClass;

class Cancel implements ResponseInterface, FromJsonInterface
{
    /** @var string|null */
    protected $shipmentNumber;

    public function __construct(?string $shipmentNumber)
    {
        $this->shipmentNumber = $shipmentNumber;
    }

    public static function fromJson(stdClass $json)
    {
        return new static($json->shipmentNumber ?? null);
    }

    public function getShipmentNumber(): ?string
    {
        return $this->shipmentNumber;
    }
}