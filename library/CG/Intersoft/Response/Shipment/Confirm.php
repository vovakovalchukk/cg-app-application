<?php
namespace CG\Intersoft\Response\Shipment;

use CG\Intersoft\ResponseInterface;
use CG\Intersoft\Response\FromXmlInterface;
use SimpleXMLElement;

class Confirm implements ResponseInterface, FromXmlInterface
{
    /** @var string */
    protected $manifestImage;
    /** @var string|null */
    protected $manifestNumber;

    public function __construct(
        ?string $manifestImage,
        ?string $manifestNumber
    ) {
        $this->manifestImage = $manifestImage;
        $this->manifestNumber = $manifestNumber;
    }

    public static function fromXml(SimpleXMLElement $xml)
    {
        if (!isset($xml->manifestDetail, $xml->manifestDetail->manifestImage)) {
            throw new \InvalidArgumentException('confirmShipmentResponse from Intersoft not in expected format');
        }

        return new static(
            (string)$xml->manifestDetail->manifestImage,
            (string)$xml->manifestDetail->manifestNumber ?? null
        );
    }

    public function getManifestImage(): string
    {
        return $this->manifestImage;
    }

    public function getManifestNumber(): ?string
    {
        return $this->manifestNumber;
    }
}