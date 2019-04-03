<?php
namespace CG\Intersoft\RoyalMail\Response\Shipment;

use CG\Intersoft\ResponseInterface;
use CG\Intersoft\Response\FromXmlInterface;
use SimpleXMLElement;


class Documents implements ResponseInterface, FromXmlInterface
{
    /** @var string */
    protected $trackingNumber;
    protected $documentType;
    protected $documentImage;

    public function __construct(string $trackingNumber, string $documentType, string $documentImage)
    {
        $this->trackingNumber = $trackingNumber;
        $this->documentType = $documentType;
        $this->documentImage = $documentImage;
    }

    public static function fromXml(SimpleXMLElement $xml): Documents
    {
        return new self(
            $xml->trackingNumber,
            $xml->documentType,
            $xml->documentImage
        );
    }

    public function getTrackingNumber(): string
    {
        return $this->trackingNumber;
    }

    public function getDocumentType(): string
    {
        return $this->documentType;
    }

    public function getDocumentImage(): string
    {
        return $this->documentImage;
    }
}