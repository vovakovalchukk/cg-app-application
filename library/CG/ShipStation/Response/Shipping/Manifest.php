<?php
namespace CG\ShipStation\Response\Shipping;

use CG\ShipStation\Messages\Carrier;
use CG\ShipStation\Messages\Downloadable;
use CG\ShipStation\ResponseAbstract;
use CG\Stdlib\DateTime;

class Label extends ResponseAbstract
{

    protected static function build($decodedJson)
    {
        var_dump($decodedJson);
    }
}