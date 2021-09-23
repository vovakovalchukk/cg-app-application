<?php
namespace CG\Intersoft\RoyalMail\Shipment;

use CG\CourierAdapter\LabelInterface;
use CG\CourierAdapter\Package\ContentInterface as PackageContent;
use CG\CourierAdapter\Package\SupportedField\CountryOfOriginInterface;
use CG\CourierAdapter\Package\SupportedField\HarmonisedSystemCodeInterface;
use CG\CourierAdapter\PackageInterface;
use CG\CourierAdapter\Package\SupportedField\ContentsInterface;
use CG\CourierAdapter\Package\SupportedField\DimensionsInterface;
use CG\CourierAdapter\Package\SupportedField\WeightInterface;
use CG\Intersoft\RoyalMail\Shipment\Package\Type as PackageType;

class Package implements
    PackageInterface,
    WeightInterface,
    DimensionsInterface,
    ContentsInterface,
    HarmonisedSystemCodeInterface,
    CountryOfOriginInterface
{
    /** @var int */
    protected $number;
    /** @var float */
    protected $weight;
    /** @var float */
    protected $height;
    /** @var float */
    protected $width;
    /** @var float */
    protected $length;
    /** @var PackageType */
    protected $type;
    /** @var PackageContent[] */
    protected $contents;

    /** @var LabelInterface */
    protected $label;
    /** @var string */
    protected $trackingReference;
    /** @var string|null */
    protected $rmShipmentNumber;
    /** @var string|null */
    protected $harmonisedSystemCode;
    /** @var string|null */
    protected $countryOfOrigin;

    public function __construct(
        int $number,
        float $weight,
        float $height,
        float $width,
        float $length,
        PackageType $type,
        array $contents = [],
        ?string $harmonisedSystemCode = null,
        ?string $countryOfOrigin = null
    ) {
        $this->number = $number;
        $this->weight = $weight;
        $this->height = $height;
        $this->width = $width;
        $this->length = $length;
        $this->type = $type;
        $this->contents = $contents;
        $this->harmonisedSystemCode = $harmonisedSystemCode;
        $this->countryOfOrigin = $countryOfOrigin;
    }

    public static function fromArray(array $array): Package
    {
        return new static(
            $array['number'],
            $array['weight'],
            $array['height'],
            $array['width'],
            $array['length'],
            $array['type'],
            $array['contents'] ?? []
        );
    }

    /**
     * @inheritdoc
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @inheritdoc
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @inheritdoc
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @inheritdoc
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @inheritdoc
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @inheritdoc
     */
    public function getDimensions()
    {
        return [
            'height' => $this->getHeight(),
            'width' => $this->getWidth(),
            'length' => $this->getLength(),
        ];
    }

    public function getType(): PackageType
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function getContents()
    {
        return $this->contents;
    }

    /**
     * @inheritdoc
     */
    public function setLabel(LabelInterface $label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @inheritdoc
     */
    public function setTrackingReference($trackingReference)
    {
        $this->trackingReference = $trackingReference;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTrackingReference()
    {
        return $this->trackingReference;
    }

    /**
     * @inheritDoc
     */
    public function getCountryOfOrigin(): ?string
    {
        return $this->countryOfOrigin;
    }

    /**
     * @inheritDoc
     */
    public function getHarmonisedSystemCode()
    {
        return $this->harmonisedSystemCode;
    }
}