<?php
namespace CG\Intersoft\RoyalMail\Request\Shipment;

use CG\CourierAdapter\Shipment\SupportedField\DeliveryInstructionsInterface;
use CG\CourierAdapter\Shipment\SupportedField\InsuranceOptionsInterface;
use CG\CourierAdapter\Shipment\SupportedField\SaturdayDeliveryInterface;
use CG\CourierAdapter\Shipment\SupportedField\SignatureRequiredInterface;
use CG\Email\Attachment\Simple;
use CG\Product\Detail\Entity as ProductDetail;
use CG\Intersoft\RoyalMail\Request\PostAbstract;
use CG\Intersoft\RoyalMail\Response\Shipment\Create as Response;
use CG\Intersoft\RoyalMail\Shipment;
use CG\Intersoft\RoyalMail\Shipment\Package;
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;
use PhpUnitsOfMeasure\PhysicalQuantity\Length;
use SimpleXMLElement;

class Create extends PostAbstract
{
    static $requestNameSpace = 'createShipmentRequest';

    const DATE_FORMAT_SHIPMENT = 'Y-m-d';
    const WEIGHT_UNIT = 'K';
    const DIMENSIONS_UNIT = 'cm';
    const PRODUCT_TYPE = 'NDX'; // DOX for documents, NDX for anything else
    const CURRENCY_DEFAULT = 'GBP';
    const MAX_LEN_REFERENCE = 20;
    const MAX_LEN_DEFAULT = 35;
    const MAX_LEN_CONTACT = 40;
    const MAX_LEN_DESCRIPTION = 255;

    /** @var Shipment */
    protected $shipment;
    /** @var string */
    protected $requestNamespace;

    public function __construct(Shipment $shipment)
    {
        $this->shipment = $shipment;
    }

    public function getUri(): string
    {
        return 'shipments/createShipmentRequest';
    }

    public function getResponseClass(): string
    {
        return Response::class;
    }

    public function asXml(): string
    {
        $xml = $this->buildXml();
        return $xml->asXml();
    }

    protected function buildXml(): SimpleXMLElement
    {
        $namespace = static::$requestNameSpace;
        $xml = new SimpleXMLElement("<{$namespace}></{$namespace}>");
        $xml = $this->addIntegrationHeader($xml);
        $shipment = $xml->addChild('shipment');
        $shipment = $this->addShipper($shipment);
        $shipment = $this->addDestination($shipment);
        $shipment = $this->addShipmentInformation($shipment);
        return $xml;
    }

    protected function convertLength(float $length): float
    {
        return (new Length($length, ProductDetail::UNIT_LENGTH))->toUnit(static::DIMENSIONS_UNIT);
    }

    protected function getSafePlace(): ?string
    {
        if (!$this->shipment instanceof DeliveryInstructionsInterface) {
            return null;
        }
        return $this->shipment->getDeliveryInstructions();
    }

    protected function addShipper(SimpleXMLElement $xml): SimpleXMLElement
    {
        $collectionAddress = $this->shipment->getCollectionAddress();
        $shipper = $xml->addChild('shipper');
        $shipper->addChild('shipperCompanyName', $this->sanitiseString($collectionAddress->getCompanyName()));
        $shipper->addChild('shipperAddressLine1', $this->sanitiseString($collectionAddress->getLine1()));
        $shipper->addChild(
        'shipperCity',
            $this->sanitiseString($collectionAddress->getLine3())
            ?: $this->sanitiseString($collectionAddress->getLine2())
            ?: $this->sanitiseString($collectionAddress->getLine4())
        );
        $shipper->addChild('shipperCountryCode', $collectionAddress->getISOAlpha2CountryCode());
        $shipper->addChild('shipperPostCode', $collectionAddress->getPostCode());
        $shipper->addChild('shipperPhoneNumber', $collectionAddress->getPhoneNumber());
        $shipper->addChild('shipperReference', $this->sanitiseString($this->shipment->getCustomerReference(), static::MAX_LEN_REFERENCE));
        return $xml;
    }

    protected function addDestination(SimpleXMLElement $xml): SimpleXMLElement
    {
        $deliveryAddress = $this->shipment->getDeliveryAddress();
        $destination = $xml->addChild('destination');
        $destination->addChild('destinationAddressLine1', $this->sanitiseString($deliveryAddress->getLine1()));
        $destination->addChild('destinationAddressLine2', $this->sanitiseString($deliveryAddress->getLine2()));
        $destination->addChild(
            'destinationCity',
            $this->sanitiseString($deliveryAddress->getLine3())
                ?: $this->sanitiseString($deliveryAddress->getLine2())
                ?: $this->sanitiseString($deliveryAddress->getLine4())
        );
        $destination->addChild(
            'destinationCounty',
            $this->sanitiseString($deliveryAddress->getLine4())
                ?: $this->sanitiseString($deliveryAddress->getLine3())
        );
        $destination->addChild('destinationCountryCode', $deliveryAddress->getISOAlpha2CountryCode());
        $destination->addChild('destinationPostCode', $deliveryAddress->getPostCode());
        $destination->addChild(
            'destinationContactName',
            $this->sanitiseString(
                $deliveryAddress->getFirstName() . ' ' . $deliveryAddress->getLastName(),
                static::MAX_LEN_CONTACT
            )
        );
        $destination->addChild('destinationPhoneNumber', $this->getDeliveryPhoneNumber());

        return $xml;
    }

    protected function addShipmentInformation(SimpleXMLElement $xml): SimpleXMLElement
    {
        $packages = $this->shipment->getPackages();
        $shipmentInformation = $xml->addChild('shipmentInformation');
        $shipmentInformation->addChild('shipmentDate', $this->shipment->getCollectionDate()->format(static::DATE_FORMAT_SHIPMENT));
        $shipmentInformation->addChild('serviceCode', $this->shipment->getDeliveryService()->getReference());
        $shipmentInformation = $this->addServiceOptions($shipmentInformation);
        $shipmentInformation = $this->addPackageInformation($shipmentInformation);
        $shipmentInformation = $this->addItemInformation($shipmentInformation);
        $shipmentInformation = $this->addShipmentOverview($shipmentInformation);
        return $xml;
    }

    protected function addServiceOptions(SimpleXMLElement $xml): SimpleXMLElement
    {
        /** @var Package $firstPackage */
        $firstPackage = $this->shipment->getPackages()[0];
        $serviceOptions = $xml->addChild('serviceOptions');
        $serviceOptions->addChild('postingLocation', $this->getPostingLocationNumber());
        $serviceOptions->addChild('serviceLevel', '01');
        $serviceOptions->addChild('serviceFormat', $firstPackage->getType()->getReference());
        $serviceOptions->addChild('safePlace', $this->getSafePlace());
        $serviceOptions = $this->addServiceEnhancements($serviceOptions);
        return $xml;
    }

    protected function getEnhancementsArray(): array
    {
        $enhancements = [];
        if ($this->shipment instanceof InsuranceOptionsInterface && $this->shipment->getInsuranceOption() != null) {
            $enhancements[] = $this->shipment->getInsuranceOption()->getReference();
        }
        if ($this->shipment instanceof SignatureRequiredInterface && $this->shipment->isSignatureRequired()) {
            $enhancements[] = static::ENHANCEMENT_SIGNATURE;
        }
        if ($this->shipment instanceof SaturdayDeliveryInterface && $this->shipment->isSaturdayDeliveryRequired()) {
            $enhancements[] = static::ENHANCEMENT_SATURDAY;
        }
        return $enhancements;
    }

    protected function addServiceEnhancements(SimpleXMLElement $xml): SimpleXMLElement
    {
        $serviceEnhancementsArray = $this->getEnhancementsArray();
        if (!count($serviceEnhancementsArray) > 0) {
            return $xml;
        }

        $serviceEnhancements = $xml->addChild('serviceEnhancements');
        foreach ($serviceEnhancementsArray as $serviceEnhancementCode) {
            $serviceEnhancements->addChild('serviceEnhancementCode', $serviceEnhancementCode);
        }
        return $xml;
    }

    protected function addPackageInformation(SimpleXMLElement $xml): SimpleXMLElement
    {
        $packages = $this->shipment->getPackages();
        $packagesXml = $xml->addChild('packages');;
        $packageId = 1;
        /** @var Package $package */
        foreach ($packages as $package) {
            $packageXml = $packagesXml->addChild('package');
            $packageXml->addChild('packageId', $packageId++);
            $packageXml->addChild('weight', $package->getWeight());
            $packageXml->addChild('length', $this->convertLength($package->getLength()));
            $packageXml->addChild('width', $this->convertLength($package->getWidth()));
            $packageXml->addChild('height', $this->convertLength($package->getHeight()));
        }
        return $xml;
    }

    protected function addItemInformation(SimpleXMLElement $xml): SimpleXMLElement
    {
        $packages = $this->shipment->getPackages();
        /** @var Package $package */
        foreach ($packages as $package) {
            foreach ($package->getContents() as $packageContents) {
                $itemInformation = $xml->addChild('itemInformation');
                $itemInformation->addChild('itemDescription', $this->sanitiseString($packageContents->getDescription(), static::MAX_LEN_DESCRIPTION));
                $itemInformation->addChild('itemQuantity', $packageContents->getQuantity());
                $itemInformation->addChild('itemValue', $packageContents->getUnitValue());
                $itemInformation->addChild('itemNetWeight', $packageContents->getWeight());
            }
        }
        return $xml;
    }

    protected function addShipmentOverview(SimpleXMLElement $xml): SimpleXMLElement
    {
        $packageOverviewDetails = $this->getOverviewDetailsArray();
        $xml->addChild('totalPackages', count($this->shipment->getPackages()));
        $xml->addChild('totalWeight', $packageOverviewDetails['totalWeight']);
        $xml->addChild('weightId', static::WEIGHT_UNIT);
        $xml->addChild('product', static::PRODUCT_TYPE);
        $xml->addChild('descriptionOfGoods', $packageOverviewDetails['description']);
        $xml->addChild('declaredValue', $packageOverviewDetails['totalValue']);
        $xml->addChild('declaredCurrencyCode', $packageOverviewDetails['currencyCode']);
        return $xml;
    }

    protected function getOverviewDetailsArray(): array
    {
        $packages = $this->shipment->getPackages();
        $details = [
            'description' => '',
            'totalValue' => 0,
            'totalWeight' => 0,
            'currencyCode' => static::CURRENCY_DEFAULT
        ];

        /** @var Package $package */
        foreach ($packages as $package) {
            foreach ($package->getContents() as $packageContent) {
                $details['description'] .=  $packageContent->getDescription() . '|';
                $details['currencyCode'] =  $packageContent->getUnitCurrency();
                $details['totalWeight'] += $packageContent->getWeight();
                $details['totalValue'] += $packageContent->getUnitValue() * $packageContent->getQuantity();
            }
            $details['description'] = $this->sanitiseString(rtrim($details['description'], '|'), static::MAX_LEN_DESCRIPTION);
        }
        return $details;
    }

    protected function sanitiseString(?string $string = null, ?int $maxLength = null): string
    {
        if ($string === null) {
            return '';
        }
        return substr($string, 0, $maxLength ?? static::MAX_LEN_DEFAULT);
    }

    protected function getDeliveryPhoneNumber(): string
    {
        // Intersoft REQUIRE a phone number but it is not enforced by us / most of our channels
        if (!strlen($this->shipment->getDeliveryAddress()->getPhoneNumber()) > 0) {
            return '00000000000';
        }
        return $this->shipment->getDeliveryAddress()->getPhoneNumber();
    }
}