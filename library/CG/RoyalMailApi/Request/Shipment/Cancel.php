<?php
namespace CG\RoyalMailApi\Request\Shipment;

use CG\RoyalMailApi\Request\DeleteAbstract;
use CG\RoyalMailApi\Response\Shipment\Cancel as Response;

class Cancel extends DeleteAbstract
{
    /** @var string */
    protected $shipmentNumber;

    public function __construct(string $shipmentNumber)
    {
        $this->shipmentNumber = $shipmentNumber;
    }

    public function getUri(): string
    {
        return $this->getShipmentNumber();
    }

    public function getResponseClass(): string
    {
        return Response::class;
    }

    public function getShipmentNumber(): string
    {
        return $this->shipmentNumber;
    }
}