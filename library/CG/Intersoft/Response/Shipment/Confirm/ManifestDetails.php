<?php
namespace CG\Intersoft\Response\Shipment\Confirm;

use SimpleXMLElement;

class ManifestDetails
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
        if (!isset($xml->manifestImage)) {
            throw new \InvalidArgumentException('confirmShipmentResponse from Intersoft not in expected format');
        }

        return new static(
            (string)$xml->manifestImage,
            (string)$xml->manifestNumber ?? null
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