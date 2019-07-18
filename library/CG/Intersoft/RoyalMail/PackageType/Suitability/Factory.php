<?php
namespace CG\Intersoft\RoyalMail\PackageType\Suitability;

use CG\Intersoft\RoyalMail\PackageType\SuitabilityInterface;

class Factory
{
    /**
     * @param string $shipmentClass Must be a class of type CG\Intersoft\RoyalMail\Shipment
     * @return SuitabilityInterface
     */
    public static function getForShipmentClass(string $shipmentClass): SuitabilityInterface
    {
        if ($shipmentClass::isInternational()) {
            // As we're currently in a static context we can't (easily) use DI so have to resort to calling new
            return new International();
        }
        return new Domestic();
    }
}