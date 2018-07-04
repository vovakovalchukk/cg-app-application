<?php
namespace CG\ShipStation\Request\Shipping;

use CG\ShipStation\Messages\Shipment;
use CG\ShipStation\RequestAbstract;
use CG\ShipStation\Response\Rates as Response;

class Rates extends RequestAbstract
{
    const METHOD = 'POST';
    const URI = '/rates';

    /** @var Shipment */
    protected $shipment;
    /** @var array */
    protected $carrierIds;

    public function __construct(Shipment $shipment, array $carrierIds)
    {
        $this->shipment = $shipment;
        $this->carrierIds = $carrierIds;
    }

    public function toArray(): array
    {
        return [
            'shipment' => $this->getShipment()->toArray(),
            'rate_options' => [
                'carrier_ids' => $this->getCarrierIds()
            ]
        ];
    }

    public function getResponseClass(): string
    {
        return Response::class;
    }

    public function getShipment(): Shipment
    {
        return $this->shipment;
    }

    public function getCarrierIds(): array
    {
        return $this->carrierIds;
    }
}