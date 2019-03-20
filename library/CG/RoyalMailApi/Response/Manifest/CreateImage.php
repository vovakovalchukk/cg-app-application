<?php
namespace CG\RoyalMailApi\Response\Manifest;

use CG\RoyalMailApi\Response\FromJsonInterface;
use CG\RoyalMailApi\ResponseInterface;
use stdClass;

class CreateImage implements ResponseInterface, FromJsonInterface
{
    /** @var string */
    protected $manifest;

    public function __construct(string $manifest)
    {
        $this->manifest = $manifest;
    }

    public function getManifest(): string
    {
        return $this->manifest;
    }

    public static function fromJson(stdClass $json)
    {
        return new static(
            (string) $json->manifest
        );
    }
}
