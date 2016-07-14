<?php
namespace CG\CourierAdapter\Provider\Implementation;

class Mapper
{
    public function fromArray(array $data)
    {
        return new Entity(
            $data['channelName'],
            $data['displayName'],
            $data['courierFactory']
        );
    }
}
