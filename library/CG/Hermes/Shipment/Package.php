<?php
namespace CG\Hermes\Shipment;

use CG\CourierAdapter\LabelInterface;
use CG\CourierAdapter\Package\ContentInterface;
use CG\CourierAdapter\Package\SupportedField\HarmonisedSystemCodeInterface;
use CG\CourierAdapter\PackageInterface;
use CG\CourierAdapter\Package\SupportedField\DimensionsInterface;
use CG\CourierAdapter\Package\SupportedField\WeightInterface;
use CG\CourierAdapter\Package\SupportedField\ContentsInterface;
use CG\Hermes\Shipment\Package\Content as PackageContents;

class Package implements
    PackageInterface,
    WeightInterface,
    DimensionsInterface,
    ContentsInterface,
    HarmonisedSystemCodeInterface
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
     * An array of the contents of the package
     *
     * @return ContentInterface[]
     */
    public function getContents()
    {
        return $this->contents;
    }

    /**
     * A string representing the harmonised system code of the item
     *
     * @return string
     */
    public function getHarmonisedSystemCode()
    {
        return $this->harmonisedSystemCode;
    }
}