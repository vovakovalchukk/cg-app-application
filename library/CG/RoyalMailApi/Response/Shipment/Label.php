<?php
namespace CG\RoyalMailApi\Response\Shipment;

use CG\RoyalMailApi\ResponseInterface;
use CG\RoyalMailApi\Response\FromJsonInterface;
use stdClass;

class Label implements ResponseInterface, FromJsonInterface
{
    const DEFAULT_FORMAT = 'PDF';

    /** @var string */
    protected $label;
    /** @var string */
    protected $format;

    public function __construct(string $label, string $format)
    {
        $this->label = $label;
        $this->format = $format;
    }

    public static function fromJson(stdClass $json)
    {
        if (!isset($json->label)) {
            throw new \InvalidArgumentException('Print label response not in expected format');
        }

        return new static(
            $json->label,
            $json->format ?? static::DEFAULT_FORMAT
        );
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getFormat(): string
    {
        return $this->format;
    }
}