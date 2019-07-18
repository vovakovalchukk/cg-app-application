<?php
namespace CG\Intersoft\RoyalMail\PackageType;

use CG\CourierAdapter\Package\SupportedField\WeightAndDimensionsInterface;
use CG\Intersoft\RoyalMail\Shipment\Package\Type as PackageType;

abstract class SuitabilityAbstract implements SuitabilityInterface
{
    public function __invoke(array $availableTypes, WeightAndDimensionsInterface $weightAndDimensions): array
    {
        $suitableTypes = [];
        $packageTypeLimits = $this->getLimits();
        /** @var PackageType $packageType */
        foreach ($availableTypes as $packageType) {
            if (!isset($packageTypeLimits[$packageType->getReference()]) ||
                $this->isWithinLimits($weightAndDimensions, $packageTypeLimits[$packageType->getReference()])
            ) {
                $suitableTypes[] = $packageType;
            }
        }
        return $suitableTypes;
    }

    protected function isWithinLimits(WeightAndDimensionsInterface $weightAndDimensions, array $limits): bool
    {
        $withinLimits = (
            (float)$weightAndDimensions->getWeight() <= $limits['weight'] &&
            (float)$weightAndDimensions->getLength() <= $limits['length'] &&
            (float)$weightAndDimensions->getWidth() <= $limits['width'] &&
            (float)$weightAndDimensions->getHeight() <= $limits['height']
        );
        if (isset($limits['total'])) {
            $totalDimensions = $weightAndDimensions->getLength() + $weightAndDimensions->getWidth() + $weightAndDimensions->getHeight();
            $withinLimits = ($withinLimits && $totalDimensions <= $limits['total']);
        }
        return $withinLimits;
    }

    abstract protected function getLimits(): array;
}