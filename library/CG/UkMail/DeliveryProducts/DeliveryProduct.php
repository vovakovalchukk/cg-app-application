<?php
namespace CG\UkMail\DeliveryProducts;

class DeliveryProduct
{
    /** @var int */
    protected $productCode;
    /** @var string */
    protected $productDescription;
    /** @var string */
    protected $transitTimeDescription;
    /** @var int */
    protected $maxTransitTime;
    /** @var int */
    protected $minTransitTime;
    /** @var string */
    protected $bulky;
    /** @var int */
    protected $businessUnitCode;
    /** @var string */
    protected $servicePointDelivery;
    /** @var array */
    protected $servicePointTypeList;
    /** @var string */
    protected $customsDeclaration;
    /** @var int */
    protected $sortOrder;

    public function __construct(
        int $productCode,
        string $productDescription,
        string $transitTimeDescription,
        int $maxTransitTime,
        int $minTransitTime,
        string $bulky,
        int $businessUnitCode,
        string $servicePointDelivery,
        array $servicePointTypeList,
        string $customsDeclaration,
        int $sortOrder
    ) {
        $this->productCode = $productCode;
        $this->productDescription = $productDescription;
        $this->transitTimeDescription = $transitTimeDescription;
        $this->maxTransitTime = $maxTransitTime;
        $this->minTransitTime = $minTransitTime;
        $this->bulky = $bulky;
        $this->businessUnitCode = $businessUnitCode;
        $this->servicePointDelivery = $servicePointDelivery;
        $this->servicePointTypeList = $servicePointTypeList;
        $this->customsDeclaration = $customsDeclaration;
        $this->sortOrder = $sortOrder;
    }

    public function getProductCode(): int
    {
        return $this->productCode;
    }

    public function getProductDescription(): string
    {
        return $this->productDescription;
    }

    public function getTransitTimeDescription(): string
    {
        return $this->transitTimeDescription;
    }

    public function getMaxTransitTime(): int
    {
        return $this->maxTransitTime;
    }

    public function getMinTransitTime(): int
    {
        return $this->minTransitTime;
    }

    public function getBulky(): string
    {
        return $this->bulky;
    }

    public function getBusinessUnitCode(): int
    {
        return $this->businessUnitCode;
    }

    public function getServicePointDelivery(): string
    {
        return $this->servicePointDelivery;
    }

    public function getServicePointTypeList(): array
    {
        return $this->servicePointTypeList;
    }

    public function getCustomsDeclaration(): string
    {
        return $this->customsDeclaration;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }
}