<?php
namespace CG\Hermes\Shipment\Package;

use CG\CourierAdapter\Package\ContentInterface;
use CG\CourierAdapter\Package\SupportedField\ContentsInterface;
use CG\Hermes\Shipment\PackageAbstract;

class International extends PackageAbstract implements ContentsInterface
{
    /** @var ContentInterface[] */
    protected $contents;

    public function __construct(int $number, float $weight, float $height, float $width, float $length, array $contents)
    {
        parent::__construct($number, $weight, $height, $width, $length);
        $this->contents = $contents;
    }

    public static function fromArray(array $array): International
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
    public function getContents()
    {
        return $this->contents;
    }
}