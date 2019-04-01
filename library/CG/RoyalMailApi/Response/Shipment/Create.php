<?php
namespace CG\RoyalMailApi\Response\Shipment;

use CG\RoyalMailApi\ResponseInterface;
use CG\RoyalMailApi\Response\FromJsonInterface;
use CG\RoyalMailApi\Response\Shipment\Completed\Item as ShipmentItem;
use stdClass;

class Create implements ResponseInterface, FromJsonInterface
{
    /** @var ShipmentItem[] */
    protected $shipmentItems;

    public function __construct(array $shipmentItems)
    {
        $this->shipmentItems = $shipmentItems;
    }

    public static function fromJson(stdClass $json)
    {
        if (!isset($json->completedShipments, $json->completedShipments[0], $json->completedShipments[0]->shipmentItems, $json->completedShipments[0]->shipmentItems[0])) {
            throw new \InvalidArgumentException('Create shipments response not in expected format');
        }
        $shipmentItems = [];
        foreach ($json->completedShipments as $completedShipmentJson) {
            foreach ($completedShipmentJson->shipmentItems as $shipmentItemJson) {
                $shipmentItems[] = ShipmentItem::fromJson($shipmentItemJson);
            }
        }
        return new static($shipmentItems);
    }

    /**
     * @return ShipmentItem[]
     */
    public function getShipmentItems(): array
    {
        return $this->shipmentItems;
    }
}