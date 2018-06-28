<?php
namespace CG\ShipStation\Messages;

class ConnectAddress extends Address
{
    public function toArray(): array
    {
        // The format of the address for connecting accounts is slightly different
        $array = parent::toArray();
        unset($array['name']);
        $array['address1'] = $array['address_line1'];
        $array['address2'] = $array['address_line2'];
        $array['city'] = $array['city_locality'];
        $array['state'] = $array['state_province'];
        unset($array['address_line1'], $array['address_line2'], $array['city_locality'], $array['state_province']);
        return $array;
    }
}