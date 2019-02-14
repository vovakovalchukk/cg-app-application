<?php
namespace CG\ShipStation\Messages;

use CG\Locale\Length as LocaleLength;
use CG\Locale\Mass as LocaleMass;
use CG\Order\Shared\Courier\Label\OrderData;
use CG\Order\Shared\Courier\Label\OrderParcelsData\ParcelData;
use CG\Order\Shared\ShippableInterface as Order;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Product\Detail\Entity as ProductDetail;

class Package
{
    const WEIGHT_UNIT = 'kilogram';
    const DIMENSION_UNIT = 'centimeter';

    const WEIGHT_UNIT_G_ABBR = 'g';
    const WEIGHT_KG_TO_G_RATIO = 1000;

    /** @var float|null */
    protected $weight;
    /** @var string|null */
    protected $weightUnit;
    /** @var float|null */
    protected $length;
    /** @var float|null */
    protected $width;
    /** @var float|null */
    protected $height;
    /** @var string|null */
    protected $dimensionsUnit;
    /** @var string|null */
    protected $packageCode;
    /** @var float|null */
    protected $insuredValue;
    /** @var string|null */
    protected $insuredCurrency;

    protected static $unitMap = [
        'g' => 'gram',
        'kg' => 'kilogram',
        'oz' => 'ounce',
        'cm' => 'centimeter',
        'in' => 'inch',
    ];

    public function __construct(
        ?float $weight,
        ?string $weightUnit,
        ?float $length,
        ?float $width,
        ?float $height,
        ?string $dimensionsUnit,
        ?string $packageCode,
        ?float $insuredValue,
        ?string $insuredCurrency
    ) {
        $this->weight = $weight;
        $this->weightUnit = $weightUnit;
        $this->length = $length;
        $this->width = $width;
        $this->height = $height;
        $this->dimensionsUnit = $dimensionsUnit;
        $this->packageCode = $packageCode;
        $this->insuredValue = $insuredValue;
        $this->insuredCurrency = $insuredCurrency;
    }

    public static function build($decodedJson): Package
    {
        return new static(
            $decodedJson->weight->value ?? null,
            $decodedJson->weight->unit ?? null,
            $decodedJson->dimensions->length ?? null,
            $decodedJson->dimensions->width ?? null,
            $decodedJson->dimensions->height ?? null,
            $decodedJson->dimensions->unit ?? null,
            $decodedJson->package_code ?? null,
            $decodedJson->insured_value->amount ?? null,
            $decodedJson->insured_value->currency ?? null
        );
    }

    public static function createFromOrderAndData(
        Order $order,
        OrderData $orderData,
        ParcelData $parcelData,
        OrganisationUnit $rootOu
    ): Package {
        $insuranceAmount = 0;
        if ((float)$orderData->getInsuranceMonetary() > 0) {
            $insuranceAmount = round($orderData->getInsuranceMonetary() / $orderData->getParcels(), 2);
        }

        $weightValue = $parcelData->getWeight();
        $weightUnit = static::$unitMap[LocaleMass::getForLocale($rootOu->getLocale())];
        if ($weightUnit == static::WEIGHT_UNIT) {
            $weightValue = $weightValue * static::WEIGHT_KG_TO_G_RATIO;
            $weightUnit = static::$unitMap[static::WEIGHT_UNIT_G_ABBR];
        }

        return new static(
            $weightValue,
            $weightUnit,
            $parcelData->getLength(),
            $parcelData->getWidth(),
            $parcelData->getHeight(),
            static::$unitMap[LocaleLength::getForLocale($rootOu->getLocale())],
            $parcelData->getPackageType() ? $parcelData->getPackageType() : $orderData->getPackageType(),
            $insuranceAmount,
            $order->getCurrencyCode()
        );
    }
    
    public function toArray(): array
    {
        $array = [];
        // ShipEngine doesn't handle null values
        if ($this->getWeight() !== null) {
            $array['weight'] = [
                'value' => $this->getWeight(),
                'unit' => $this->getWeightUnit(),
            ];
        }
        if ($this->getLength() !== null || $this->getWidth() !== null || $this->getHeight() !== null) {
            $array['dimensions'] = [
                'length' => $this->getLength(),
                'width' => $this->getWidth(),
                'height' => $this->getHeight(),
                'unit' => $this->getDimensionsUnit(),
            ];
        }
        if ($this->getInsuredValue() !== null) {
            $array['insured_value'] = [
                'amount' => $this->getInsuredValue(),
                'currency' => $this->getInsuredCurrency(),
            ];
        }
        if ($this->getPackageCode() !== null) {
            $array['package_code'] = $this->getPackageCode();
        }
        return $array;
    }

    public function getWeight(): ?float
    {
        return $this->weight;
    }

    public function setWeight(?float $weight): self
    {
        $this->weight = $weight;
        return $this;
    }

    public function getWeightUnit(): ?string
    {
        return $this->weightUnit;
    }

    public function setWeightUnit(?string $weightUnit): self
    {
        $this->weightUnit = $weightUnit;
        return $this;
    }

    public function getLength(): ?float
    {
        return $this->length;
    }

    public function setLength(?float $length): self
    {
        $this->length = $length;
        return $this;
    }

    public function getWidth(): ?float
    {
        return $this->width;
    }

    public function setWidth(?float $width): self
    {
        $this->width = $width;
        return $this;
    }

    public function getHeight(): ?float
    {
        return $this->height;
    }

    public function setHeight(?float $height): self
    {
        $this->height = $height;
        return $this;
    }

    public function getDimensionsUnit(): ?string
    {
        return $this->dimensionsUnit;
    }

    public function setDimensionsUnit(?string $dimensionsUnit): self
    {
        $this->dimensionsUnit = $dimensionsUnit;
        return $this;
    }

    public function getPackageCode(): ?string
    {
        return $this->packageCode;
    }

    public function setPackageCode(?string $packageCode): self
    {
        $this->packageCode = $packageCode;
        return $this;
    }

    public function getInsuredValue(): ?float
    {
        return $this->insuredValue;
    }

    public function setInsuredValue(?float $insuredValue): self
    {
        $this->insuredValue = $insuredValue;
        return $this;
    }

    public function getInsuredCurrency(): ?string
    {
        return $this->insuredCurrency;
    }

    public function setInsuredCurrency(?string $insuredCurrency): self
    {
        $this->insuredCurrency = $insuredCurrency;
        return $this;
    }
}