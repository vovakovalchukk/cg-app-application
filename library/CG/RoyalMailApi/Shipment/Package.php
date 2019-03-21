<?php
namespace CG\RoyalMailApi\Shipment;

use CG\CourierAdapter\LabelInterface;
use CG\CourierAdapter\Package\ContentInterface as PackageContent;
use CG\CourierAdapter\PackageInterface;
use CG\CourierAdapter\Package\SupportedField\ContentsInterface;
use CG\CourierAdapter\Package\SupportedField\DimensionsInterface;
use CG\CourierAdapter\Package\SupportedField\WeightInterface;

class Package implements PackageInterface, WeightInterface, DimensionsInterface, ContentsInterface
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

    protected $type;
    /** @var PackageContent[] */
    protected $contents;

    /** @var LabelInterface */
    protected $label;
    /** @var string */
    protected $trackingReference;
    /** @var string|null */
    protected $rmShipmentNumber;

    public function __construct(
        int $number,
        float $weight,
        float $height,
        float $width,
        float $length,
        $type,
        array $contents = []
    ) {
        $this->number = $number;
        $this->weight = $weight;
        $this->height = $height;
        $this->width = $width;
        $this->length = $length;
        $this->type = $type;
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

    public function getType()
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

    public function getRmShipmentNumber(): ?string
    {
        return $this->rmShipmentNumber;
    }

    public function setRmShipmentNumber(string $rmShipmentNumber)
    {
        $this->rmShipmentNumber = $rmShipmentNumber;
        return $this;
    }
}