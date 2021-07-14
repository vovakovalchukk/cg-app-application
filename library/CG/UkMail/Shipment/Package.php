<?php
namespace CG\UkMail\Shipment;

use CG\CourierAdapter\LabelInterface;
use CG\CourierAdapter\Package\ContentInterface;
use CG\CourierAdapter\Package\SupportedField\ContentsInterface;
use CG\CourierAdapter\Package\SupportedField\CountryOfOriginInterface;
use CG\CourierAdapter\Package\SupportedField\DimensionsInterface;
use CG\CourierAdapter\Package\SupportedField\HarmonisedSystemCodeInterface;
use CG\CourierAdapter\Package\SupportedField\WeightInterface;
use CG\CourierAdapter\PackageInterface;
use CG\CourierAdapter\Provider\Implementation\Package\Content as PackageContents;

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
    /** @var string */
    protected $harmonisedSystemCode;
    /** @var string */
    protected $countryOfOrigin;

    /** @var LabelInterface */
    protected $label;
    /** @var string */
    protected $trackingReference;
    /** @var PackageContents[]  */
    protected $contents;

    public function __construct(
        int $number,
        float $weight,
        float $height,
        float $width,
        float $length,
        array $contents
    ) {
        $this->number = $number;
        $this->weight = $weight;
        $this->height = $height;
        $this->width = $width;
        $this->length = $length;
        $this->contents = $contents;
    }

    public static function fromArray(array $array): Package
    {
        return new static(
            $array['number'],
            $array['weight'],
            $array['height'],
            $array['width'],
            $array['length'],
            $array['contents']
        );
    }

    public function getContents()
    {
        return $this->contents;
    }

    public function getCountryOfOrigin(): ?string
    {
        return $this->countryOfOrigin;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getLength()
    {
        return $this->length;
    }

    public function getDimensions()
    {
        return [
            'height' => $this->getHeight(),
            'width' => $this->getWidth(),
            'length' => $this->getLength(),
        ];
    }

    public function getHarmonisedSystemCode()
    {
        return $this->harmonisedSystemCode;
    }

    public function setLabel(LabelInterface $label)
    {
        $this->label = $label;
        return $this;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setTrackingReference($trackingReference)
    {
        $this->trackingReference = $trackingReference;
        return $this;
    }

    public function getTrackingReference()
    {
        return $this->trackingReference;
    }

    public function getNumber()
    {
        return $this->number;
    }

    public function getWeight()
    {
        return $this->weight;
    }
}