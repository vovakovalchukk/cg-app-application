<?php
namespace CG\ShipStation\Response\Connect;

use CG\ShipStation\EntityTrait\CarrierTrait;
use CG\ShipStation\ResponseAbstract;

class Response extends ResponseAbstract
{
    use CarrierTrait;

    protected function build($decodedJson)
    {
        return $this->setCarrierId($decodedJson);
    }
}
