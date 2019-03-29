<?php
namespace CG\RoyalMailApi\Response;

use CG\RoyalMailApi\ResponseInterface;
use CG\RoyalMailApi\Response\FromJsonInterface;
use stdClass;

class Generic implements ResponseInterface, FromJsonInterface
{
    /** @var stdClass */
    protected $json;

    public function __construct(stdClass $json)
    {
        $this->json = $json;
    }

    public static function fromJson(stdClass $json)
    {
        return new static($json);
    }

    public function __toString(): string
    {
        return json_encode($this->json);
    }
}