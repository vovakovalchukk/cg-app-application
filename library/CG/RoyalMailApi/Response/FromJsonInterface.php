<?php
namespace CG\RoyalMailApi\Response;

use stdClass;

interface FromJsonInterface
{
    public static function fromJson(stdClass $json);
}