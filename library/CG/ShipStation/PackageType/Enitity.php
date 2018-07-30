<?php

namespace CG\ShipStation\PackageType;

class Entity
{
    /** @var string */
    protected $displayName;
    /** @var float */
    protected $height;
    /** @var float */
    protected $length;
    /** @var string */
    protected $locality;
    /** @var string */
    protected $type;
    /** @var string */
    protected $restrictionType;
    /** @var string */
    protected $service;
    /** @var float */
    protected $weight;
    /** @var float */
    protected $width;

    public function __construct(?string $displayName, ?float $height, ?float $length, ?string $locality, ?string $type, ?string $restrictionType, ?string $service, ?float $weight, ?float $width)
    {
        $this->height = $height;
        $this->length = $length;
        $this->locality = $locality;
        $this->restrictionType = $restrictionType;
        $this->service = $service;
        $this->type = $type;
        $this->weight = $weight;
        $this->width = $width;
    }

    public function toArray()
    {
        return [
            'displayName' => $this->displayName,
            'height' => $this->height,
            'length' => $this->length,
            'locality' => $this->locality,
            'type' => $this->type,
            'restrictionType' => $this->restrictionType,
            'service' => $this->service,
            'weight' => $this->weight,
            'width' => $this->width
        ];
    }

    public function setType(?string $type): Entity
    {
        $this->type = $type;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setDisplayName(?string $displayName): Entity
    {
        $this->displayName = $displayName;
        return $this;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
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

    public function getLength(): ?float
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

    public function setService(?string $service): Entity
    {
        $this->service = $service;
        return $this;
    }

    public function getService(): ?string
    {
        return $this->service;
    }

    // Required by collection
    public function getId()
    {
        return $this->type;
    }

    public function setLocality(?string $locality): Entity
    {
        $this->locality = $locality;
    }

    public function getLocality(): ?string
    {
        return $this->locality;
    }

    public function setRestrictionType(?string $restrictionType): Entity
    {
        $this->restrictionType = $restrictionType;
        return $this;
    }

    public function getRestrictionType(): ?string
    {
        return $this->restrictionType;
    }
}