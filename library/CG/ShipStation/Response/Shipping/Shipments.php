<?php
namespace CG\ShipStation\Response\Shipping;

use CG\ShipStation\ResponseAbstract;

class Shipments extends ResponseAbstract
{
    protected $hasErrors;
    protected $shipments;

    public function __construct(bool $hasErrors, Shipment ...$shipments)
    {
        $this->hasErrors = $hasErrors;
        $this->shipments = $shipments;
    }

    protected static function build($decodedJson): Shipments
    {
        $shipments = [];
        foreach ($decodedJson->shipments as $shipmentJson) {
            $shipments[] = Shipment::build($shipmentJson);
        }

        return new static(
            $decodedJson->has_errors,
            ...$shipments
        );
    }

    public function hasErrors(): bool
    {
        return $this->hasErrors;
    }

    public function getShipments(): array
    {
        return $this->shipments;
    }
}