<?php
namespace CG\Intersoft\RoyalMail\Response\Shipment;

use CG\Intersoft\Response\FromXmlInterface;
use CG\Intersoft\RoyalMail\Package;
use CG\Intersoft\ResponseInterface;
use SimpleXMLElement;

class Create implements ResponseInterface, FromXmlInterface
{

    /** @var Package[] */
    protected $packages;
    /** @var string */
    protected $carrierCode;
    /** @var string */
    protected $labelImage;
    /** @var string */
    protected $labelImageFormat;

    public function __construct(
        array $packages,
        string $carrierCode,
        string $labelImage,
        string $labelImageFormat
    ) {
        $this->packages = $packages;
        $this->carrierCode = $carrierCode;
        $this->labelImage = $labelImage;
        $this->labelImageFormat = $labelImageFormat;
    }

    public static function fromXml(SimpleXMLElement $xml): Create
    {
        $completedShipment = $xml->completedShipment;
        $packages = [];
        foreach ($completedShipment->packages as $package) {
            $packages[] = Package::fromXml($package);
        }
        return new self(
            $packages,
            $completedShipment->carrierCode,
            $completedShipment->labelImage,
            $completedShipment->labelImageFormat
        );
    }

    public static function fromArray(array $data): Create
    {
        return new self(
            $data['packages'],
            $data['carrierCode'],
            $data['labelImage'],
            $data['labelImageFormat']
        );
    }

    public function getPackages(): array
    {
        return $this->packages;
    }

    public function getCarrierCode(): string
    {
        return $this->carrierCode;
    }

    public function getLabelImage(): string
    {
        return $this->labelImage;
    }

    public function getLabelImageFormat(): string
    {
        return $this->labelImageFormat;
    }
}