<?php
namespace CG\ShipStation\PackageType;

class Mapper
{
    public function fromArray(array $data)
    {
        return new Entity(
            $data['displayName'] ?? null,
            $data['height'] ?? null,
            $data['length'] ?? null,
            $data['locality'] ?? null,
            $data['type'] ?? null,
            $data['restrictionType'] ?? null,
            $data['service'] ?? null,
            $data['weight'] ?? null,
            $data['width'] ?? null
        );
    }
}
