<?php
namespace CG\ShipStation\PackageType;

class Mapper
{
    public function fromArray(array $data)
    {
        return new Entity(
            $data['code'] ?? null,
            $data['height'] ?? null,
            $data['length'] ?? null,
            $data['locality'] ?? null,
            $data['name'] ?? null,
            $data['restrictionType'] ?? null,
            $data['service'] ?? null,
            $data['weight'] ?? null,
            $data['width'] ?? null
        );
    }
}
