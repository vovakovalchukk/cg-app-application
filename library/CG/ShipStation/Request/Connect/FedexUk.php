<?php
namespace CG\ShipStation\Request\Connect;

class FedexUk extends Fedex
{
    public static function fromArray(array $params): ConnectInterface
    {
        // Only the state_province / state field is different for the UK compared to US
        if (isset($params['state_province']) || isset($params['state province'])) {
            $params['state'] = $params['state_province'] ?? $params['state province'];
            unset($params['state_province'], $params['state province']);
        }
        return parent::fromArray($params);
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        // Only the state_province / state field is different for the UK compared to US
        $array['state_province'] = $array['state'];
        unset($array['state']);
        return $array;
    }
}