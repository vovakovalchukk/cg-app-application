<?php
namespace CG\Intersoft\Response;

use CG\Intersoft\ResponseInterface;
use CG\Intersoft\Response\FromXmlInterface;
use SimpleXMLElement;

class Generic implements ResponseInterface, FromXmlInterface
{
    /** @var SimpleXMLElement */
    protected $xml;

    public function __construct(SimpleXMLElement $xml)
    {
        $this->xml = $xml;
    }

    public static function fromXml(SimpleXMLElement $xml)
    {
        return new static($xml);
    }

    public function __toString(): string
    {
        return (string)$this->xml;
    }
}