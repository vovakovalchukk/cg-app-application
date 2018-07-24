<?php
namespace CG\Hermes\Shipment;

use CG\Hermes\Shipment\Package\Domestic as DomesticPackage;

class SignatureDomestic extends SignatureAbstract
{
    /**
     * @inheritdoc
     */
    public static function getPackageClass()
    {
        return DomesticPackage::class;
    }

    /**
     * @inheritdoc
     */
    public static function createPackage(array $packageDetails)
    {
        return DomesticPackage::fromArray($packageDetails);
    }
}