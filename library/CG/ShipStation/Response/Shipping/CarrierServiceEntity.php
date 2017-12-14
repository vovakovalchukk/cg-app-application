<?php
namespace CG\ShipStation\Response\Shipping;

use CG\ShipStation\Entity\Carrier;
use CG\ShipStation\Entity\CarrierService;

class CarrierServiceEntity implements \JsonSerializable
{
    /** @var  Carrier */
    protected $carrier;
    /** @var  CarrierService */
    protected $carrierService;

    public function __construct(
        Carrier $carrier,
        CarrierService $carrierService
    ) {
        $this->carrier = $carrier;
        $this->carrierService = $carrierService;
    }

    public function jsonSerialize()
    {
        return [
            'carrier' => $this->getCarrier(),
            'carrierService' => $this->getCarrierService(),
        ];
    }

    public function getCarrier(): Carrier
    {
        return $this->carrier;
    }

    public function getCarrierService(): CarrierService
    {
        return $this->carrierService;
    }
}
