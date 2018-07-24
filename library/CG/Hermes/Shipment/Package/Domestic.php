<?php
namespace CG\Hermes\Shipment\Package;

use CG\Hermes\Shipment\PackageAbstract;

class Domestic extends PackageAbstract
{
    public static function fromArray(array $array): Domestic
    {
        return new static(
            $array['number'],
            $array['weight'],
            $array['height'],
            $array['width'],
            $array['length']
        );
    }
}