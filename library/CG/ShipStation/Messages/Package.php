<?php
namespace CG\ShipStation\Messages;

use CG\Locale\Length as LocaleLength;
use CG\Locale\Mass as LocaleMass;
use CG\Order\Shared\Courier\Label\OrderData;
use CG\Order\Shared\Courier\Label\OrderParcelsData\ParcelData;
use CG\Order\Shared\ShippableInterface as Order;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;

class Package
{
    const WEIGHT_UNIT = 'kilogram';
    const DIMENSION_UNIT = 'centimeter';

    const WEIGHT_UNIT_GRAM_ABBR = 'g';
    const WEIGHT_UNIT_KG_ABBR = 'kg';

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
    /** @var string|null */
    protected $reference1;

    protected static $unitMap = [
        'g' => 'gram',
        'kg' => 'kilogram',
        'oz' => 'ounce',
        'cm' => 'centimeter',
        'in' => 'inch',
    ];

    protected static $unitConversion = [
        self::WEIGHT_UNIT_KG_ABBR => self::WEIGHT_UNIT_GRAM_ABBR
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
        ?string $insuredCurrency,
        ?string $reference1
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
        $this->reference1 = $reference1;
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
            $decodedJson->insured_value->currency ?? null,
            $decodedJson->label_messages->reference1 ?? null
        );
    }

    public static function createFromOrderAndData(
        Order $order,
        OrderData $orderData,
        ParcelData $parcelData,
        OrganisationUnit $rootOu,
        ?string $reference
    ): Package {
        $insuranceAmount = 0;
        if ((float)$orderData->getInsuranceMonetary() > 0) {
            $insuranceAmount = round($orderData->getInsuranceMonetary() / $orderData->getParcels(), 2);
        }

        $localeMass = LocaleMass::getForLocale($rootOu->getLocale());

        $weightValue = $parcelData->getWeight();
        $weightUnit = static::$unitMap[$localeMass];

        if (isset(static::$unitConversion[$localeMass])) {
            $weightValue = round((new Mass($weightValue, $localeMass))->toUnit(static::$unitConversion[$localeMass]), 2);
            $weightUnit = static::$unitMap[static::$unitConversion[$localeMass]];
        }

        return new static(
            $weightValue,
            $weightUnit,
            $parcelData->getLength(),
            $parcelData->getWidth(),
            $parcelData->getHeight(),
            static::$unitMap[LocaleLength::getForLocale($rootOu->getLocale())],
            $parcelData->getPackageType(),
            $insuranceAmount,
            $order->getCurrencyCode(),
            $reference
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
        if ($this->getReference1() !== null) {
            $array['label_messages'] = [
                'reference1' => $this->getReference1()
            ];
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

    public function getReference1(): ?string
    {
        return $this->reference1;
    }

    public function setReference1(?string $reference1): Package
    {
        $this->reference1 = $reference1;
        return $this;
    }
}