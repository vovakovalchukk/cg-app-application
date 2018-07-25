<?php

namespace CG\ShipStation\PackageType;

class Entity
{
    /** @var float */
    protected $height;
    /** @var float */
    protected $length;
    /** @var float */
    protected $weight;
    /** @var float */
    protected $width;

    public function __construct(?float $height, ?float $length, ?float $weight, ?float $width)
    {
        $this->height = $height;
        $this->length = $length;
        $this->weight = $weight;
        $this->width = $width;
    }

    public function toArray()
    {
        return [
            'height' => $this->height,
            'length' => $this->length,
            'weight' => $this->weight,
            'width' => $this->width
        ];
    }

    public function setHeight(?float $height): Entity
    {
        $this->height = $height;
        return $this;
    }

    public function getHeight(): ?float
    {
        return $this->height;
    }

    public function setLength(?float $length): Entity
    {
        $this->length = $length;
        return $this;
    }

    public function getLength(): ?null
    {
        return $this->length;
    }

    public function setWeight(?float $weight): Entity
    {
        $this->weight = $weight;
        return $this;
    }

    public function getWeight(): ?float
    {
        return $this->weight;
    }

    public function setWidth(?float $width): Entity
    {
        $this->width = $width;
        return $this;
    }

    public function getWidth(): ?float
    {
        return $this->width;
    }
}