<?php
namespace CG\UkMail\Consignment\Domestic;

class Parcel
{
    /** @var int|null */
    protected $length;
    /** @var int|null */
    protected $width;
    /** @var int|null */
    protected $height;

    public function __construct(?int $length, ?int $width, ?int $height)
    {
        $this->length = $length;
        $this->width = $width;
        $this->height = $height;
    }

    public function toArray(): array
    {
        return [
            'length' => $this->getLength(),
            'width' => $this->getWidth(),
            'height' => $this->getHeight(),
        ];
    }

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }
}