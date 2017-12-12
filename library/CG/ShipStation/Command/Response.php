<?php
namespace CG\ShipStation\Command;

use CG\ShipStation\ResponseAbstract;

class Response extends ResponseAbstract
{
    protected $json;

    protected function build($decodedJson)
    {
        $this->json = $decodedJson;
        return $this;
    }
}
