<?php
namespace CG\RoyalMailApi\Response\Shipment;

use CG\RoyalMailApi\ResponseInterface;
use CG\RoyalMailApi\Response\FromJsonInterface;
use stdClass;

class Documents implements ResponseInterface, FromJsonInterface
{
    /** @var string */
    protected $internationalDocument;

    public function __construct(string $internationalDocument)
    {
        $this->internationalDocument = $internationalDocument;
    }

    public static function fromJson(stdClass $json)
    {
        if (!isset($json->internationalDocument)) {
            throw new \InvalidArgumentException('Print documents response not in expected format');
        }
        return new static($json->internationalDocument);
    }

    public function getInternationalDocument(): string
    {
        return $this->internationalDocument;
    }
}