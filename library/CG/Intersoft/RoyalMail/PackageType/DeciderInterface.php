<?php
namespace CG\Intersoft\RoyalMail\PackageType;

use CG\CourierAdapter\Package\SupportedField\WeightAndDimensionsInterface;
use CG\Intersoft\RoyalMail\Shipment\Package\Type as PackageType;

interface DeciderInterface
{
    /**
     * @param PackageType[] $availableTypes
     * @param WeightAndDimensionsInterface[] $weightAndDimensions
     * @return PackageType
     */
    public function __invoke(array $availableTypes, array $weightAndDimensions): PackageType;
}