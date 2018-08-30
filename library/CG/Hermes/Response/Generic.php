<?php
namespace CG\Hermes\Response;

use CG\Hermes\ResponseInterface;
use SimpleXMLElement;

class Generic implements ResponseInterface
{
    /** @var SimpleXMLElement */
    protected $xml;

    public function __construct(SimpleXMLElement $xml)
    {
        $this->xml = $xml;
    }

    public static function createFromXml(SimpleXMLElement $xml): Generic
    {
        return new static($xml);
    }

    public function __toString(): string
    {
        return $this->xml->asXML();
    }
}