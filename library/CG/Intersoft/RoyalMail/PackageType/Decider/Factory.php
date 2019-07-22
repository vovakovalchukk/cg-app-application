<?php
namespace CG\Intersoft\RoyalMail\PackageType\Decider;

use CG\Intersoft\RoyalMail\PackageType\Decider;
use CG\Intersoft\RoyalMail\PackageType\Limits\Domestic;
use CG\Intersoft\RoyalMail\PackageType\Limits\International;

class Factory
{
    /**
     * @param string $shipmentClass Must be a class of type CG\Intersoft\RoyalMail\Shipment
     * @return Decider
     */
    public static function getForShipmentClass(string $shipmentClass): Decider
    {
        if ($shipmentClass::isInternational()) {
            // As we're currently in a static context we can't (easily) use DI so have to resort to calling new
            $limits = new International();
        } else {
            $limits = new Domestic();
        }
        return new Decider($limits->getLimits());
    }
}