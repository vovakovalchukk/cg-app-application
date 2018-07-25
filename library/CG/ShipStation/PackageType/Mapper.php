<?php
namespace CG\ShipStation\PackageType;

class Mapper
{
    public function fromArray(array $data)
    {
        return new Entity(
            $data['height'],
            $data['length'],
            $data['locality'],
            $data['name'],
            $data['service'],
            $data['weight'],
            $data['width']
        );
    }
}
