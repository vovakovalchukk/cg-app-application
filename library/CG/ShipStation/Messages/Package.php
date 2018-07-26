<?php
namespace CG\ShipStation\Messages;

use CG\Locale\Length as LocaleLength;
use CG\Locale\Mass as LocaleMass;
use CG\Order\Shared\Entity as Order;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Product\Detail\Entity as ProductDetail;

class Package
{
    const WEIGHT_UNIT = 'kilogram';
    const DIMENSION_UNIT = 'centimeter';

    /** @var float */
    protected $weight;
    /** @var string */
    protected $weightUnit;
    /** @var float */
    protected $length;
    /** @var float */
    protected $width;
    /** @var float */
    protected $height;
    /** @var string */
    protected $dimensionsUnit;
    /** @var float */
    protected $insuredValue;
    /** @var string */
    protected $insuredCurrency;

    protected static $unitMap = [
        'kg' => 'kilogram',
        'oz' => 'ounce',
        'cm' => 'centimeter',
        'in' => 'inch',
    ];

    public function __construct(
        float $weight,
        string $weightUnit,
        float $length,
        float $width,
        float $height,
        string $dimensionsUnit,
        float $insuredValue,
        string $insuredCurrency
    ) {
        $this->weight = $weight;
        $this->weightUnit = $weightUnit;
        $this->length = $length;
        $this->width = $width;
        $this->height = $height;
        $this->dimensionsUnit = $dimensionsUnit;
        $this->insuredValue = $insuredValue;
        $this->insuredCurrency = $insuredCurrency;
    }

    public static function build($decodedJson): Package
    {
        return new static(
            $decodedJson->weight->value,
            $decodedJson->weight->unit,
            $decodedJson->dimensions->length,
            $decodedJson->dimensions->width,
            $decodedJson->dimensions->height,
            $decodedJson->dimensions->unit,
            $decodedJson->insured_value->amount,
            $decodedJson->insured_value->currency
        );
    }

    public static function createFromOrderAndData(Order $order, array $orderData, array $parcelData, OrganisationUnit $rootOu): Package
    {
        $insuranceAmount = 0;
        if (isset($orderData['insuranceMonetary']) && (float)$orderData['insuranceMonetary'] > 0) {
            $insuranceAmount = round($orderData['insuranceMonetary'] / $orderData['parcels'], 2);
        }
        return new static(
            $parcelData['weight'],
            static::$unitMap[LocaleMass::getForLocale($rootOu->getLocale())],
            $parcelData['length'],
            $parcelData['width'],
            $parcelData['height'],
            static::$unitMap[LocaleLength::getForLocale($rootOu->getLocale())],
            $insuranceAmount,
            $order->getCurrencyCode()
        );
    }
    
    public function toArray(): array
    {
        return [
            'weight' => [
                'value' => $this->getWeight(),
                'unit' => $this->getWeightUnit(),
            ],
            'dimensions' => [
                'length' => $this->getLength(),
                'width' => $this->getWidth(),
                'height' => $this->getHeight(),
                'unit' => $this->getDimensionsUnit(),
            ],
            'insured_value' => [
                'amount' => $this->getInsuredValue(),
                'currency' => $this->getInsuredCurrency(),
            ],
        ];
    }

    public function getWeight(): float
    {
        return $this->weight;
    }

    /**
     * @return self
     */
    public function setWeight(float $weight)
    {
        $this->weight = $weight;
        return $this;
    }

    public function getWeightUnit(): string
    {
        return $this->weightUnit;
    }

    /**
     * @return self
     */
    public function setWeightUnit(string $weightUnit)
    {
        $this->weightUnit = $weightUnit;
        return $this;
    }

    public function getLength(): float
    {
        return $this->length;
    }

    /**
     * @return self
     */
    public function setLength(float $length)
    {
        $this->length = $length;
        return $this;
    }

    public function getWidth(): float
    {
        return $this->width;
    }

    /**
     * @return self
     */
    public function setWidth(float $width)
    {
        $this->width = $width;
        return $this;
    }

    public function getHeight(): float
    {
        return $this->height;
    }

    /**
     * @return self
     */
    public function setHeight(float $height)
    {
        $this->height = $height;
        return $this;
    }

    public function getDimensionsUnit(): string
    {
        return $this->dimensionsUnit;
    }

    /**
     * @return self
     */
    public function setDimensionsUnit(string $dimensionsUnit)
    {
        $this->dimensionsUnit = $dimensionsUnit;
        return $this;
    }

    public function getInsuredValue(): float
    {
        return $this->insuredValue;
    }

    /**
     * @return self
     */
    public function setInsuredValue(float $insuredValue)
    {
        $this->insuredValue = $insuredValue;
        return $this;
    }

    public function getInsuredCurrency(): string
    {
        return $this->insuredCurrency;
    }

    /**
     * @return self
     */
    public function setInsuredCurrency(string $insuredCurrency)
    {
        $this->insuredCurrency = $insuredCurrency;
        return $this;
    }
}