<?php
namespace CG\Intersoft\Response\Shipment;

use CG\Intersoft\ResponseInterface;
use CG\Intersoft\Response\Shipment\Confirm\ManifestDetails;
use CG\Intersoft\Response\FromXmlInterface;
use function CG\Stdlib\mergePdfData;
use SimpleXMLElement;

class Confirm implements ResponseInterface, FromXmlInterface
{
    /** @var ManifestDetails[] */
    protected $manifestDetails;

    public function __construct(array $manifestDetails)
    {
        $this->manifestDetails = $manifestDetails;
    }

    public static function fromXml(SimpleXMLElement $xml)
    {
        if (!isset($xml->manifestDetail, $xml->manifestDetail->manifestImage)) {
            throw new \InvalidArgumentException('confirmShipmentResponse from Intersoft not in expected format');
        }

        $manifestDetails = [];
        foreach ($xml->manifestDetail as $manifestDetailNode) {
            $manifestDetails[] = ManifestDetails::fromXml($manifestDetailNode);
        }

        return new static($manifestDetails);
    }

    /**
     * @return ManifestDetails[]
     */
    public function getManifestDetails(): array
    {
        return $this->manifestDetails;
    }

    public function getManifestImage(): string
    {
        $manifestImages = [];
        foreach ($this->getManifestDetails() as $manifestDetail) {
            $manifestImages[] = base64_decode($manifestDetail->getManifestImage());
        }
        $rawPdf = (count($manifestImages) == 1 ? $manifestImages[0] : mergePdfData($manifestImages));
        return base64_encode($rawPdf);
    }

    public function getManifestNumber(): ?string
    {
        $manifestNumbers = [];
        foreach ($this->getManifestDetails() as $manifestDetail) {
            $manifestNumbers[] = $manifestDetail->getManifestNumber();
        }
        return implode('|', array_filter($manifestNumbers));
    }
}