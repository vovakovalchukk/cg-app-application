<?php
namespace CG\ShipStation\Request\Shipping;

use CG\ShipStation\Messages\Shipment;
use CG\ShipStation\RequestAbstract;
use CG\ShipStation\Response\Shipping\Shipments as Response;

class Shipments extends RequestAbstract
{
    const METHOD = 'POST';
    const URI = '/shipments';

    /** @var Shipment[] */
    protected $shipments;

    public function __construct(Shipment ...$shipments)
    {
        $this->shipments = $shipments;
    }

    public function toArray(): array
    {
        return ['shipments' => $this->getShipmentsArray()];
    }

    protected function getShipmentsArray(): array
    {
        $shipments = [];
        foreach ($this->shipments as $shipment) {
            $shipments[] = $shipment->toArray();
        }
        return $shipments;
    }

    public function getResponseClass(): string
    {
        return Response::class;
    }

    // No static factory method, use Mapper class
}