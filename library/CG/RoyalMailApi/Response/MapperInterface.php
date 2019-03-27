<?php
namespace CG\RoyalMailApi\Response;

use stdClass;

interface MapperInterface
{
    public function fromJson(stdClass $json);
}