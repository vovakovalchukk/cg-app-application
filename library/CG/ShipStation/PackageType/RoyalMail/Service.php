<?php
namespace CG\ShipStation\PackageType\RoyalMail;

use CG\Product\Detail\Collection as ProductDetailCollection;
use CG\Product\Detail\Entity as ProductDetail;

class Service
{
    const DEFAULT_TYPE = 'parcel';

    /** @var Collection */
    protected $domestic;
    /** @var Collection */
    protected $international;

    public function __construct(array $domesticConfig, array $internationalConfig)
    {
        $this->domestic = new Collection(Entity::class, __CLASS__);
        foreach ($domesticConfig as $config) {
            $this->domestic->attach(Entity::fromArray($config));
        }
        $this->international = new Collection(Entity::class, __CLASS__);
        foreach ($internationalConfig as $config) {
            $this->international->attach(Entity::fromArray($config));
        }
    }

    public function getForProductDetails(ProductDetailCollection $productDetails, string $countryCode): ?Entity
    {
        if ($productDetails->count() == 1) {
            return $this->getForProductDetail($productDetails->getFirst(), $countryCode);
        }
        $packageTypes = $this->getForCountryCode($countryCode);
        return $packageTypes->getById(static::DEFAULT_TYPE);
    }

    public function getForProductDetail(ProductDetail $productDetail, string $countryCode): ?Entity
    {
        $packageTypes = $this->getForCountryCode($countryCode);
        foreach ($packageTypes as $packageType) {
            if ($packageType->supportsProductWeightAndDimensions($productDetail)) {
                return $packageType;
            }
        }
        return $packageTypes->getById(static::DEFAULT_TYPE);
    }

    public function getDefault(string $countryCode): ?Entity
    {
        $packageTypes = $this->getForCountryCode($countryCode);
        return $packageTypes->getById(static::DEFAULT_TYPE);
    }

    public function getForCountryCode(string $countryCode): Collection
    {
        if ($this->isDomestic($countryCode)) {
            return $this->domestic;
        }
        return $this->international;
    }

    protected function isDomestic(string $countryCode): bool
    {
        return $countryCode == 'GB';
    }
}