<?php
namespace CG\CourierAdapter\Provider\Adapter;

class Mapper
{
    public function fromArray(array $data)
    {
        return new Entity(
            $data['channelName'],
            $data['displayName'],
            $data['courierInterfaceClosure']
        );
    }
}
