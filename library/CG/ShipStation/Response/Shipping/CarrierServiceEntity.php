<?php
namespace CG\ShipStation\Response\Shipping;

use CG\ShipStation\EntityTrait\CarrierServiceTrait;

class CarrierServiceEntity
{
    use CarrierServiceTrait;

    public function __construct(
        string $carrierId,
        string $carrierCode,
        string $serviceCode,
        string $name,
        bool $domestic,
        bool $international,
        bool $multiPackageSupported
    ) {
        $this->setCarrierId($carrierId)
            ->setCarrierCode($carrierCode)
            ->setServiceCode($serviceCode)
            ->setName($name)
            ->setDomestic($domestic)
            ->setInternational($international)
            ->setMultiPackageSupported($multiPackageSupported);
    }
}
