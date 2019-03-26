<?php
namespace CG\Hermes\Request;

use CG\CourierAdapter\AddressInterface;
use CG\Hermes\DeliveryService;
use CG\Hermes\RequestInterface;
use CG\Hermes\Response\RouteDeliveryCreatePreadviceAndLabel as Response;
use CG\Hermes\Shipment;
use CG\Hermes\Shipment\Package;
use CG\Hermes\Shipment\Package\Content as PackageContent;
use CG\Product\Detail\Entity as ProductDetail;
use PhpUnitsOfMeasure\PhysicalQuantity\Length;
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;
use SimpleXMLElement;

class RouteDeliveryCreatePreadviceAndLabel implements RequestInterface
{
    const METHOD = 'POST';
    const URI = 'routeDeliveryCreatePreadviceAndLabel';
    const SOURCE_OF_REQUEST = 'CLIENTWS';
    const DEFAULT_MAX_LEN = 32;
    const MAX_PHONE_LEN = 15;
    const MAX_EMAIL_LEN = 80;
    const MAX_REF_LEN = 20;
    const MAX_INSTRUCT_LEN = 32;
    const MAX_SKU_LEN = 30;
    const MAX_DESC_LEN = 2000;
    const MAX_HS_CODE_LENGTH = 10;
    const WEIGHT_UNIT = 'g';
    const DIMENSION_UNIT = 'cm';
    const DEFAULT_VALUE = 100;
    const DUTY_UNPAID_FLAG = 'U';
    const COUNTRY_CODE_NETHERLANDS = 'NL';
    const NETHERLANDS_ADDRESS_1_REGEX = '/(?:\d+[a-z]*)$/';

    /** @var Shipment */
    protected $shipment;
    /** @var DeliveryService */
    protected $deliveryService;

    /** @var SimpleXMLElement */
    protected $xml;

    public function __construct(Shipment $shipment, DeliveryService $deliveryService)
    {
        $this->shipment = $shipment;
        $this->deliveryService = $deliveryService;
    }

    public function getMethod(): string
    {
        return static::METHOD;
    }

    public function getUri(): string
    {
        return static::URI;
    }

    public function asXML(): string
    {
        return $this->mapShipmentToSimpleXml()->asXML();
    }

    protected function mapShipmentToSimpleXml(): SimpleXMLElement
    {
        if ($this->xml) {
            return $this->xml;
        }

        $credentials = $this->shipment->getAccount()->getCredentials();
        $this->xml = new SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?><deliveryRoutingRequest></deliveryRoutingRequest>'
        );
        $this->xml->addChild('clientId', $credentials['clientId']);
        $this->xml->addChild('clientName', $credentials['clientName']);
        $this->xml->addChild('creationDate', (new \DateTime())->format('c'));
        $this->xml->addChild('sourceOfRequest', static::SOURCE_OF_REQUEST);
        $deliveryRoutingRequestEntriesNode = $this->xml->addChild('deliveryRoutingRequestEntries');
        /** @var Package $package */
        foreach ($this->shipment->getPackages() as $package) {
            $deliveryRoutingRequestEntryNode = $deliveryRoutingRequestEntriesNode->addChild('deliveryRoutingRequestEntry');
            $this->addCustomerToRoutingRequestNode($deliveryRoutingRequestEntryNode);
            $this->addParcelToRoutingRequestNode($deliveryRoutingRequestEntryNode, $package);
            $this->addServicesToRoutingRequestNode($deliveryRoutingRequestEntryNode);
            $this->addSenderToRoutingRequestNode($deliveryRoutingRequestEntryNode);
            $deliveryRoutingRequestEntryNode->addChild('expectedDespatchDate',
                $this->shipment->getCollectionDate()->format('c'));
            $deliveryRoutingRequestEntryNode->addChild('countryOfOrigin',
                $this->shipment->getCollectionAddress()->getISOAlpha2CountryCode());
        }

        return $this->xml;
    }

    protected function addCustomerToRoutingRequestNode(SimpleXMLElement $deliveryRoutingRequestEntryNode): void
    {
        $deliveryAddress = $this->shipment->getDeliveryAddress();
        $customerNode = $deliveryRoutingRequestEntryNode->addChild('customer');
        $this->addAddressToCustomerNode($customerNode, $deliveryAddress);
        $customerNode->addChild('homePhoneNo',
            $this->sanitiseString($deliveryAddress->getPhoneNumber(), static::MAX_PHONE_LEN)
        );
        $customerNode->addChild('email',
            $this->sanitiseString($deliveryAddress->getEmailAddress(), static::MAX_EMAIL_LEN)
        );
        $customerNode->addChild('customerReference1',
            $this->sanitiseString($this->shipment->getCustomerReference(), static::MAX_REF_LEN)
        );
        $customerNode ->addChild('deliveryMessage',
            $this->sanitiseString($this->shipment->getDeliveryInstructions(), static::MAX_INSTRUCT_LEN)
        );
    }

    protected function addAddressToCustomerNode(SimpleXMLElement $customerNode, AddressInterface $deliveryAddress): void
    {
        $line2 = $deliveryAddress->getLine2();
        $city = $deliveryAddress->determineCityFromAddressLines();
        $region = $deliveryAddress->determineRegionFromAddressLines();

        $customerAddressNode = $customerNode->addChild('address');
        $customerAddressNode->addChild('firstName', $this->sanitiseString($deliveryAddress->getFirstName()));
        $customerAddressNode->addChild('lastName', $this->sanitiseString($deliveryAddress->getLastName()));
        $this->addAddressLine1($customerAddressNode, $deliveryAddress);

        if ($line2 && $line2 != $city && $line2 != $region) {
            $customerAddressNode->addChild('addressLine2', $this->sanitiseString($line2));
        }
        $customerAddressNode->addChild('city', $this->sanitiseString($city));
        if ($region && $region != $city) {
            $customerAddressNode->addChild('region', $this->sanitiseString($region));
        }
        $customerAddressNode->addChild('postCode', $this->sanitiseString($deliveryAddress->getPostCode()));
        $customerAddressNode->addChild('countryCode', strtoupper($deliveryAddress->getISOAlpha2CountryCode()));
    }

    protected function addParcelToRoutingRequestNode(SimpleXMLElement $deliveryRoutingRequestEntryNode, Package $package): void
    {
        $parcelNode = $deliveryRoutingRequestEntryNode->addChild('parcel');
        $parcelNode->addChild('weight', $this->convertWeight($package->getWeight()));
        $parcelNode->addChild('length', $this->convertDimension($package->getLength()));
        $parcelNode->addChild('width', $this->convertDimension($package->getWidth()));
        $parcelNode->addChild('depth', $this->convertDimension($package->getHeight()));
        $parcelNode->addChild('girth', 0);
        $parcelNode->addChild('combinedDimension', 0);
        $parcelNode->addChild('volume', 0);
        $parcelNode->addChild('value', $this->calculateValueOfPackage($package));
        $parcelNode->addChild('dutyPaid', static::DUTY_UNPAID_FLAG);
        $parcelNode->addChild('currency', $this->determineCurrencyOfPackage($package));
        $parcelNode->addChild('numberOfItems', $this->determineNumberOfItems($package));
        $parcelNode->addChild('description', $this->getPackageDescription($package));
        $parcelNode->addChild('originOfParcel', $this->shipment->getCollectionAddress()->getISOAlpha2CountryCode());
        // The below may need adding as part of TAC-378, we are awaiting a response from our customer
//        $parcelNode->addChild('dutyPaidValue', '');
//        $parcelNode->addChild('vatValue', '');
        $contents = $parcelNode->addChild('contents');
        $this->addContentsToParcelNode($contents, $package);
    }

    protected function addContentsToParcelNode(SimpleXMLElement $contents, Package $package)
    {
        /** @var PackageContent $packageContent */
        foreach ($package->getContents() as $packageContent) {
            $content = $contents->addChild('content');
            $content->addChild('skuDescription', $this->sanitiseString($packageContent->getName() . "\n" . $packageContent->getDescription(),static::MAX_DESC_LEN));
            $content->addChild('hsCode', $this->sanitiseString($packageContent->getHsCode(), static::MAX_HS_CODE_LENGTH));
            $content->addChild('countryOfManufacture', 'GB');
            $content->addChild('itemQuantity', $packageContent->getQuantity());
            $content->addChild('itemWeight', $this->convertValueToMinorUnits($packageContent->getWeight()));
            $content->addChild('value', $this->convertValueToMinorUnits($packageContent->getUnitValue()));
            $content->addChild('skuCode', $this->sanitiseString($packageContent->getSku(), static::MAX_SKU_LEN));
        }
        return;
    }

    protected function addServicesToRoutingRequestNode(SimpleXMLElement $deliveryRoutingRequestEntryNode): void
    {
        $servicesNode = $deliveryRoutingRequestEntryNode->addChild('services');
        if ($this->deliveryService->getSpecificDay()) {
            $this->addSpecificDayToServicesNode($servicesNode, $this->deliveryService->getSpecificDay());
        }
        $servicesNode->addChild('nextDay', $this->sanitiseBoolean($this->deliveryService->isNextDay()));
        $servicesNode->addChild('signature', $this->sanitiseBoolean($this->shipment->isSignatureRequired()));
    }

    protected function addSpecificDayToServicesNode(SimpleXMLElement $servicesNode, int $specificDay): void
    {
        $servicesNode->addChild('statedDay')->addChild('statedDayIndicator', $specificDay);
    }

    protected function addSenderToRoutingRequestNode(SimpleXMLElement $deliveryRoutingRequestEntryNode): void
    {
        $sendersAddress = $this->shipment->getCollectionAddress();
        $sendersAddressNode = $deliveryRoutingRequestEntryNode->addChild('senderAddress');
        $sendersAddressNode->addChild('addressLine1', $this->sanitiseString($sendersAddress->getLine1()));
        $sendersAddressNode->addChild('addressLine2', $this->sanitiseString($sendersAddress->getLine2()));
        $sendersAddressNode->addChild('addressLine3', $this->sanitiseString($sendersAddress->getLine3()));
        $sendersAddressNode->addChild('addressLine4', $this->sanitiseString($sendersAddress->getLine4()));
    }

    protected function sanitiseString(?string $string = null, ?int $maxLength = null): string
    {
        if ($string === null) {
            return '';
        }
        return substr($string, 0, $maxLength ?? static::DEFAULT_MAX_LEN);
    }

    protected function sanitiseBoolean(bool $boolean): string
    {
        return $boolean ? 'true' : 'false';
    }

    protected function convertWeight(float $weight): float
    {
        return (new Mass($weight, ProductDetail::UNIT_MASS))->toUnit(static::WEIGHT_UNIT);
    }

    protected function convertDimension(float $dimension): float
    {
        return (new Length($dimension, ProductDetail::UNIT_LENGTH))->toUnit(static::DIMENSION_UNIT);
    }

    protected function determineCurrencyOfPackage(Package $package): string
    {
        if (empty($package->getContents())) {
            return '';
        }
        return $this->sanitiseString($package->getContents()[0]->getUnitCurrency());
    }

    protected function calculateValueOfPackage(Package $package): float
    {
        $value = 0;
        foreach ($package->getContents() as $content) {
            $value += $content->getUnitValue() * $content->getQuantity();
        }
        // Value must be in pence / cents
        return $this->convertValueToMinorUnits($value);
    }

    protected function convertValueToMinorUnits(float $value): float
    {
        // MOST currencies have 2dp but a few don't. If we ever deal in those for this courier then this will need to change.
        return $value * 100;
    }

    public function getResponseClass(): string
    {
        return Response::class;
    }

    protected function addAddressLine1(SimpleXMLElement $customerAddressNode, AddressInterface $deliveryAddress): void
    {
        // Right now we only need to do anything different for Netherlands. If we need to handle differences for additional countries in the future
        // this should be refactored
        if (strtoupper($deliveryAddress->getISOAlpha2CountryCode() !== static::COUNTRY_CODE_NETHERLANDS)) {
            $customerAddressNode->addChild('streetName', $this->sanitiseString($deliveryAddress->getLine1()));
            return;
        }

        $streetName = preg_replace_callback(
            static::NETHERLANDS_ADDRESS_1_REGEX,
            function($matches) use ($customerAddressNode) {
                $customerAddressNode->addChild('houseNo', $matches[0]);
                return '';
            },
            $deliveryAddress->getLine1(),
            1
            );
        $customerAddressNode->addChild('streetName', $this->sanitiseString(rtrim($streetName)));
    }

    protected function determineNumberOfItems(Package $package): int
    {
        $itemCount = 0;
        foreach ($package->getContents() as $packageContent) {
            $itemCount+= $packageContent->getQuantity();
        }
        return $itemCount;
    }

    protected function getPackageDescription(Package $package): string
    {
        $description = '';
        foreach ($package->getContents() as $packageContent) {
            $description .= $packageContent->getDescription() . "\n";
        }
        return $this->sanitiseString(rtrim($description));
    }
}