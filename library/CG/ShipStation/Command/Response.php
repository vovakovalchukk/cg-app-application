<?php
namespace CG\ShipStation\Command;

use CG\ShipStation\ResponseAbstract;

class Response extends ResponseAbstract
{
    protected $json;

    protected static function build($decodedJson)
    {
        return (new static())->setJsonResponse(json_encode($decodedJson));
    }
}
