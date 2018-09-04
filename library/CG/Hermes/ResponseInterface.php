<?php
namespace CG\Hermes;

use SimpleXMLElement;

interface ResponseInterface
{
    public static function createFromXml(SimpleXMLElement $xml);
}