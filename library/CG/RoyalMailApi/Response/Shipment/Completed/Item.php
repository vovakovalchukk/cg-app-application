<?php
namespace CG\RoyalMailApi\Response\Shipment\Completed;

use stdClass;

class Item
{
    /** @var string */
    protected $shipmentNumber;
    /** @var string */
    protected $itemId;
    /** @var string */
    protected $status;
    /** @var string|null */
    protected $label;

    public function __construct(string $shipmentNumber, string $itemId, string $status, ?string $label)
    {
        $this->shipmentNumber = $shipmentNumber;
        $this->itemId = $itemId;
        $this->status = $status;
        $this->label = $label;
    }

    public static function fromJson(stdClass $json): Item
    {
        return new static(
            $json->shipmentNumber,
            $json->itemID,
            $json->status,
            $json->label ?? null
        );
    }

    public function getShipmentNumber(): string
    {
        return $this->shipmentNumber;
    }

    public function getItemId(): string
    {
        return $this->itemId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }
}