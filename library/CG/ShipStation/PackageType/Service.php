<?php

namespace CG\ShipStation\PackageType;

use CG\Product\Detail\Entity as ProductDetailEntity;

class Service
{
    /** @var Collection */
    protected $packageTypes;
    /** @var Mapper */
    protected $mapper;

    public const USPS_DEFAULT_PACKAGE_NAME = 'Package';
    public const USPS_DEFAULT_PACKAGE_CODE = 'package';

    public const USPS_LENGTH_AND_GIRTH_RESTRICTION_TYPE = 'lengthAndGirthRestriction';
    public const USPS_VOLUME_RESTRICTION_TYPE = 'volumeRestriction';

    public const USPS_VOLUME_TOTAL_RESTRICTION = 36;
    public const USPS_VOLUME_ANY_SINGLE_SIDE_RESTRICTION = 24;
    public const USPS_LENGTH_AND_GIRTH_TOTAL_RESTRICTION = 108;

    public function __construct(Mapper $mapper, array $packageTypesConfig)
    {
        if ($this->packageTypes === null) {
            $this->setMapper($mapper);
            $collection = new Collection(Entity::class, __CLASS__);
            foreach ($packageTypesConfig as $locality => $servicePackages) {
                foreach ($servicePackages as $serviceName => $packageTypes) {
                    foreach ($packageTypes as $packageName => $config) {
                        $data = $config;
                        $data['service'] = $serviceName;
                        $data['name'] = $packageName;
                        $data['locality'] = $locality;
                        $collection->attach($this->mapper->fromArray($data));
                    }
                }
            }
            $this->setPackageTypes($collection);
        }
    }

    public function setMapper(Mapper $mapper): Service
    {
        $this->mapper = $mapper;
        return $this;
    }

    public function setPackageTypes(Collection $packageTypes): Service
    {
        $this->packageTypes = $packageTypes;
        return $this;
    }

    public function getDomesticPackages(Collection $packageTypes = null): Collection
    {
        if ($packageTypes === null) {
            $packageTypes = $this->packageTypes;
        }
        return $packageTypes->getBy('locality', 'Domestic');
    }

    public function getInternationalPackages(Collection $packageTypes = null): Collection
    {
        if ($packageTypes === null) {
            $packageTypes = $this->packageTypes;
        }
        return $packageTypes->getBy('locality', 'International');
    }

    public function getPackageTypesForService(string $service, Collection $packageTypes = null): Collection
    {
        if ($packageTypes === null) {
            $packageTypes = $this->packageTypes;
        }
        return $packageTypes->getBy('service', $service);
    }

    public function isPackageSuitableForItemWeightAndDimensions(Entity $packageType, ProductDetailEntity $product)
    {
        if ($packageType->getRestrictionType() === static::USPS_VOLUME_RESTRICTION_TYPE) {
            return $this->doesItemMeetPackageTypeVolumeRequirements($product);
        } elseif ($packageType->getRestrictionType() === static::USPS_LENGTH_AND_GIRTH_RESTRICTION_TYPE) {
            return $this->doesItemMeetPackageTypeGirthAndLengthRequirements($product);
        } else {
            return $this->doesItemMeetPackageTypeStandardDimensionRequirements($packageType, $product);
        }
    }

    public function doesItemMeetPackageTypeVolumeRequirements(ProductDetailEntity $product): bool
    {
        $ssr = static::USPS_VOLUME_ANY_SINGLE_SIDE_RESTRICTION;
        if ($product->getWidth() > $ssr
            || $product->getLength() > $ssr
            || $product->getHeight() > $ssr
        ) {
            return false;
        }

        if (($product->getWidth() + $product->getLength() + $product->getHeight()) > static::USPS_VOLUME_TOTAL_RESTRICTION) {
            return false;
        }

        return true;
    }

    public function doesItemMeetPackageTypeGirthAndLengthRequirements(ProductDetailEntity $product): bool
    {
        if (
            ($product->getWidth() + $product->getWidth() + $product->getHeight() + $product->getHeight())
            + $product->getLength() > static::USPS_LENGTH_AND_GIRTH_TOTAL_RESTRICTION
        ) {
            return false;
        }

        return true;
    }

    public function doesItemMeetPackageTypeStandardDimensionRequirements(Entity $packageType, ProductDetailEntity $product): bool
    {
        if (
            $product->getLength() > $packageType->getLength()
            || $product->getWidth() > $packageType->getWidth()
            || $product->getHeight() > $product->getHeight()
        ) {
            return false;
        }

        return true;
    }
}