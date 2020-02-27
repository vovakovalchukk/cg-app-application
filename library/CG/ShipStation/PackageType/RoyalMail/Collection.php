<?php
namespace CG\ShipStation\PackageType\RoyalMail;

use CG\Stdlib\Collection as StdlibCollection;

class Collection extends StdlibCollection
{
    public function toOptionsArrayOfArrays(?Entity $selectedPackageType = null): array
    {
        $options = [];
        /** @var PackageType $packageType */
        foreach ($this as $packageType) {
            $selected = $selectedPackageType && $packageType->getCode() == $selectedPackageType->getCode();
            $options[$packageType->getCode()] = $packageType->toOptionArray($selected);
        }
        return $options;
    }
}