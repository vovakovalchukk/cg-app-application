<?php
namespace CG\Intersoft;

use SimpleXMLElement;

class IntersoftXml extends SimpleXMLElement
{
    public function prependChild(string $xmlString)
    {
        $xmlString = str_replace("<?xml version=\"1.0\"?>\n", '', $xmlString);
        $dom = dom_import_simplexml($this);
        $fragment = $dom->ownerDocument->createDocumentFragment();
        $fragment->appendXML($xmlString);

        $new = $dom->insertBefore(
            $fragment,
            $dom->firstChild
        );

        return $new;
    }
}