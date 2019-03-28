<?php
namespace CG\Intersoft\Response;

use SimpleXMLElement;

interface MapperInterface
{
    public function fromXml(SimpleXMLElement $xml);
}