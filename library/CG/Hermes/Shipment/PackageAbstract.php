<?php
namespace CG\Hermes\Shipment;

use CG\CourierAdapter\LabelInterface;
use CG\CourierAdapter\PackageInterface;
use CG\CourierAdapter\Package\SupportedField\DimensionsInterface;
use CG\CourierAdapter\Package\SupportedField\WeightInterface;

abstract class PackageAbstract implements PackageInterface, WeightInterface, DimensionsInterface
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
    /** @var LabelInterface */
    protected $label;
    /** @var string */
    protected $trackingReference;

    public function __construct(int $number, float $weight, float $height, float $width, float $length)
    {
        $this->number = $number;
        $this->weight = $weight;
        $this->height = $height;
        $this->width = $width;
        $this->length = $length;
    }

    abstract public static function fromArray(array $array);

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
}