<?php
namespace CG\Intersoft\RoyalMail\PackageType;

use CG\CourierAdapter\Package\SupportedField\WeightAndDimensionsInterface;

interface SuitabilityInterface
{
    public function __invoke(array $availableTypes, WeightAndDimensionsInterface $weightAndDimensions): array;
}