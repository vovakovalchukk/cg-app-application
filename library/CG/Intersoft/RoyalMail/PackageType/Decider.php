<?php
namespace CG\Intersoft\RoyalMail\PackageType;

use CG\CourierAdapter\Package\SupportedField\WeightAndDimensionsInterface;
use CG\Intersoft\RoyalMail\Shipment\Package\Type as PackageType;
use CG\Product\Detail\Entity as ProductDetail;
use PhpUnitsOfMeasure\PhysicalQuantity\Length;
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;

class Decider
{
    const UNIT_MASS = 'kg';
    const UNIT_LENGTH = 'cm';

    /** @var array */
    protected $typeLimits;

    protected $totalFields = ['weight', 'total'];
    protected $dimensionFields = ['length', 'width', 'height'];

    public function __construct(array $typeLimits)
    {
        $this->typeLimits = $typeLimits;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(array $availableTypes, array $weightAndDimensions): PackageType
    {
        $weightAndDimensions = $this->convertWeightAndDimensions($weightAndDimensions);
        if (count($weightAndDimensions) == 1) {
            return $this->forSingleItem($availableTypes, array_pop($weightAndDimensions));
        }
        return $this->forMultipleItems($availableTypes, $weightAndDimensions);
    }

    protected function convertWeightAndDimensions(array $weightAndDimensions): array
    {
        foreach ($weightAndDimensions as $item) {
            if (ProductDetail::UNIT_MASS != static::UNIT_MASS) {
                $item->setWeight((new Mass((float)$item->getWeight(), ProductDetail::UNIT_MASS))->toUnit(static::UNIT_MASS));
            }
            if (ProductDetail::UNIT_LENGTH != static::UNIT_LENGTH) {
                $item->setLength((new Length((float)$item->getLength(), ProductDetail::UNIT_LENGTH))->toUnit(static::UNIT_LENGTH));
                $item->setWidth((new Length((float)$item->getWidth(), ProductDetail::UNIT_LENGTH))->toUnit(static::UNIT_LENGTH));
                $item->setHeight((new Length((float)$item->getHeight(), ProductDetail::UNIT_LENGTH))->toUnit(static::UNIT_LENGTH));
            }
        }
        return $weightAndDimensions;
    }

    /**
     * @param PackageType[] $availableTypes
     */
    protected function forSingleItem(array $availableTypes, WeightAndDimensionsInterface $item): PackageType
    {
        /** @var PackageType $packageType */
        foreach ($availableTypes as $packageType) {
            if (isset($this->typeLimits[$packageType->getReference()]) &&
                $this->isWithinLimits($item, $this->typeLimits[$packageType->getReference()])
            ) {
                return $packageType;
            }
        }
        // Default to the last which should be the biggest
        return array_pop($availableTypes);
    }

    /**
     * @param PackageType[] $availableTypes
     * @param WeightAndDimensionsInterface[] $items
     */
    protected function forMultipleItems(array $availableTypes, array $items): PackageType
    {
        $biggestItem = $this->extractItemWithBiggestSingleDimension($items);
        $biggestPossiblePackageType = $this->getBiggestPossible($availableTypes);
        return $this->determineMostSuitablePackageType($biggestItem, $items, $biggestPossiblePackageType, $availableTypes);
    }

    protected function determineMostSuitablePackageType(
        WeightAndDimensionsInterface $biggestItem,
        array $remainingItems,
        PackageType $biggestPossiblePackageType,
        array $availableTypes,
        PackageType $packageType = null
    ): PackageType {
        if (!$packageType) {
            $packageType = $this->forSingleItem($availableTypes, $biggestItem);
        }
        // If there's no bigger type then there's no point in continuing the search
        if ($packageType->getReference() == $biggestPossiblePackageType->getReference()) {
            return $packageType;
        }
        $packageTypeLimits = $this->typeLimits[$packageType->getReference()];
        $spaceRemaining = $this->deductItemFromWeightAndDimensions($biggestItem, $packageTypeLimits);
        foreach ($remainingItems as $item) {
            $enoughLeftInTotals = $this->isEnoughLeftInWeightAndTotal($item, $spaceRemaining);
            $dimensionWithEnoughSpace = $this->determineDimensionWithEnoughSpaceForItem($item, $spaceRemaining);
            if ($enoughLeftInTotals == false || $dimensionWithEnoughSpace == null) {
                $nextPackageType = $this->getNextPackageTypeUp($packageType, $availableTypes);
                // Recurse!
                return $this->determineMostSuitablePackageType(
                    $biggestItem, $remainingItems, $biggestPossiblePackageType, $availableTypes, $nextPackageType
                );
            }
            $spaceRemaining = $this->deductItemFromOneDimensionAndTotals($item, $spaceRemaining, $dimensionWithEnoughSpace);
        }
        // Everything fit
        return $packageType;
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

    /**
     * * @param WeightAndDimensionsInterface[] $items
     */
    protected function extractItemWithBiggestSingleDimension(array &$items): WeightAndDimensionsInterface
    {
        /** @var WeightAndDimensionsInterface $biggest */
        $biggest = null;
        $biggestIndex = null;
        foreach ($items as $index => $item) {
            if ($biggest == null ||
                $item->getHeight() > $biggest->getHeight() ||
                $item->getWidth() > $biggest->getWidth() ||
                $item->getLength() > $biggest->getLength()
            ) {
                $biggest = $item;
                $biggestIndex = $index;
            }
        }
        unset($items[$biggestIndex]);
        return $biggest;
    }

    /**
     * @param PackageType[] $availableTypes
     */
    protected function getBiggestPossible(array $availableTypes): PackageType
    {
        $sortedTypeReferences = array_keys($this->typeLimits);
        usort($availableTypes, function(PackageType $typeA, PackageType $typeB) use ($sortedTypeReferences)
        {
            $relativeSizeA = array_search($typeA->getReference(), $sortedTypeReferences);
            $relativeSizeB = array_search($typeB->getReference(), $sortedTypeReferences);
            return $relativeSizeA <=> $relativeSizeB;
        });
        return array_pop($availableTypes);
    }

    protected function deductItemFromWeightAndDimensions(WeightAndDimensionsInterface $item, array $availableDimensions): array
    {
        $dimensionsToDeduct = array_keys($availableDimensions);
        unset($dimensionsToDeduct['total']);
        return $this->deductFromSpecificDemensions($item, $availableDimensions, $dimensionsToDeduct);
    }

    protected function deductFromSpecificDemensions(WeightAndDimensionsInterface $item, array $availableDimensions, array $dimensionsToDeduct): array
    {
        foreach ($availableDimensions as $name => $value) {
            if (!in_array($name, $dimensionsToDeduct)) {
                continue;
            }
            $getter = 'get'.ucfirst($name);
            $availableDimensions[$name] -= $item->{$getter}();
            if (isset($availableDimensions['total']) && in_array($name, $this->dimensionFields)) {
                $availableDimensions['total'] -= $item->{$getter}();
            }
        }
        return $availableDimensions;
    }

    protected function isEnoughLeftInWeightAndTotal(WeightAndDimensionsInterface $item, array $availableDimensions): bool
    {
        foreach ($availableDimensions as $name => $value) {
            if (!in_array($name, $this->totalFields)) {
                continue;
            }
            $getter = 'get'.ucfirst($name);
            $availableDimensions[$name] -= $item->{$getter}();
            if ($availableDimensions[$name] < 0) {
                return false;
            }
        }
        return true;
    }

    protected function determineDimensionWithEnoughSpaceForItem(WeightAndDimensionsInterface $item, array $dimensions): ?string
    {
        foreach ($dimensions as $name => $value) {
            if (!in_array($name, $this->dimensionFields)) {
                continue;
            }
            $getter = 'get'.ucfirst($name);
            if ($value - $item->{$getter}() >= 0) {
                return $name;
            }
        }
        return null;
    }

    protected function deductItemFromOneDimensionAndTotals(WeightAndDimensionsInterface $item, array $availableDimensions, string $dimension): array
    {
        $dimensionsToDeduct = [$dimension, 'weight'];
        return $this->deductFromSpecificDemensions($item, $availableDimensions, $dimensionsToDeduct);
    }

    protected function getNextPackageTypeUp(PackageType $currentPackageType, array $availableTypes): PackageType
    {
        for ($pos = 0; $pos < count($availableTypes); $pos++) {
            if ($availableTypes[$pos]->getReference() != $currentPackageType->getReference()) {
                continue;
            }
            return $availableTypes[$pos+1];
        }
        // This shouldn't happen as, separately, we're testing if we hit the biggest type
        throw new \OutOfRangeException('There are no types bigger than ' . $currentPackageType->getDisplayName());
    }
}