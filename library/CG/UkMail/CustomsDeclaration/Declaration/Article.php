<?php
namespace CG\UkMail\CustomsDeclaration\Declaration;

use CG\Locale\CountryNameByAlpha3Code;
use CG\Stdlib\Exception\Storage as StorageException;

class Article
{
    /** @var string */
    protected $commodityCode;
    /** @var string */
    protected $goodsDescription;
    /** @var int */
    protected $quantity;
    /** @var float */
    protected $unitValue;
    /** @var float */
    protected $unitWeight;
    /** @var string */
    protected $countryOfManufacture;

    public function __construct(
        string $commodityCode,
        string $goodsDescription,
        int $quantity,
        float $unitValue,
        float $unitWeight,
        string $countryOfManufacture
    ) {
        $this->commodityCode = $commodityCode;
        $this->goodsDescription = $goodsDescription;
        $this->quantity = $quantity;
        $this->unitValue = $unitValue;
        $this->unitWeight = $unitWeight;
        $this->countryOfManufacture = $countryOfManufacture;
    }

    public function toArray(): array
    {
        return [
            'commodityCode' => $this->getCommodityCode(),
            'goodsDescription' => $this->getGoodsDescription(),
            'quantity' => $this->getQuantity(),
            'unitValue' => $this->getUnitValue(),
            'unitWeight' => $this->getUnitWeight(),
            'countryofManufacture' => $this->getCountryOfManufacture(),
        ];
    }

    public function getCommodityCode(): string
    {
        return $this->commodityCode;
    }

    public function getGoodsDescription(): string
    {
        return $this->goodsDescription;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getUnitValue(): float
    {
        return $this->unitValue;
    }

    public function getUnitWeight(): float
    {
        return $this->unitWeight;
    }

    public function getCountryOfManufacture(): string
    {
        try {
            return CountryNameByAlpha3Code::getCountryAlpha3CodeFromCountryAlpha2Code($this->countryOfManufacture);
        } catch (\Throwable $exception) {
            throw new StorageException($exception->getMessage(), 400, $exception);
        }
    }
}