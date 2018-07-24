<?php
namespace CG\Hermes\Shipment;

use CG\Hermes\Shipment\Package\International as InternationalPackage;

class SignatureInternational extends SignatureAbstract
{
    /**
     * @inheritdoc
     */
    public static function getPackageClass()
    {
        return InternationalPackage::class;
    }

    /**
     * @inheritdoc
     */
    public static function createPackage(array $packageDetails)
    {
        return InternationalPackage::fromArray($packageDetails);
    }
}