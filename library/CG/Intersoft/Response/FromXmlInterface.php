<?php
namespace CG\Intersoft\Response;

use SimpleXMLElement;

interface FromXmlInterface
{
    public static function fromXml(SimpleXMLElement $xml);
}