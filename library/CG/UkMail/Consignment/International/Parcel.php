<?php
namespace CG\UkMail\Consignment\International;

class Parcel
{
    /** @var int */
    protected $length;
    /** @var int */
    protected $width;
    /** @var int */
    protected $height;
    /** @var float */
    protected $weight;

    public function __construct(int $length, int $width, int $height, float $weight)
    {
        $this->length = $length;
        $this->width = $width;
        $this->height = $height;
        $this->weight = $weight;
    }

    public function toArray(): array
    {
        return [
            'length' => $this->getLength(),
            'width' => $this->getWidth(),
            'height' => $this->getHeight(),
            'weight' => $this->getWeight(),
        ];
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getWeight(): float
    {
        return $this->weight;
    }
}