<?php
namespace CG\ShipStation\PackageType\RoyalMail;

class Entity
{
    /** @var string */
    protected $name;
    /** @var string */
    protected $code;
    /** @var float */
    protected $weight;
    /** @var float */
    protected $width;
    /** @var float */
    protected $length;
    /** @var float */
    protected $height;

    public function __construct(string $name, string $code, float $weight, float $width, float $length, float $height)
    {
        $this->name = $name;
        $this->code = $code;
        $this->weight = $weight;
        $this->width = $width;
        $this->length = $length;
        $this->height = $height;
    }

    public static function fromArray(array $array): self
    {
        return new static(
            $array['name'],
            $array['code'],
            $array['weight'],
            $array['width'],
            $array['length'],
            $array['height']
        );
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getWeight(): float
    {
        return $this->weight;
    }

    public function getWidth(): float
    {
        return $this->width;
    }

    public function getLength(): float
    {
        return $this->length;
    }

    public function getHeight(): float
    {
        return $this->height;
    }

    // Required for use in Collection
    public function getId(): string
    {
        return $this->getCode();
    }
}