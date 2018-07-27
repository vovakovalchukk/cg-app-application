<?php
namespace CG\Hermes\Request;

use CG\CourierAdapter\AddressInterface;
use CG\Hermes\DeliveryService;
use CG\Hermes\RequestInterface;
use CG\Hermes\Response\RouteDeliveryCreatePreadviceAndLabel as Response;
use CG\Hermes\Shipment;
use CG\Hermes\Shipment\Package;
use CG\Product\Detail\Entity as ProductDetail;
use PhpUnitsOfMeasure\PhysicalQuantity\Length;
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;
use SimpleXMLElement;

class RouteDeliveryCreatePreadviceAndLabel implements RequestInterface
{
    const METHOD = 'POST';
    const URI = 'routeDeliveryCreatePreadviceAndLabel';
    const SOURCE_OF_REQUEST = 'CLIENTWS';
    const DEFAULT_MAX_LEN = 50;
    const MAX_PHONE_LEN = 15;
    const MAX_EMAIL_LEN = 80;
    const MAX_REF_LEN = 20;
    const MAX_INSTRUCT_LEN = 32;
    const MAX_SKU_LEN = 30;
    const MAX_DESC_LEN = 2000;
    const WEIGHT_UNIT = 'g';
    const DIMENSION_UNIT = 'cm';
    const DEFAULT_VALUE = 100;
    const DUTY_UNPAID_FLAG = 'U';

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
        $routingRequestNode = $this->xml
            ->addChild('clientId', $credentials['clientId'])
            ->addChild('clientName', $credentials['clientName'])
            ->addChild('sourceOfRequest', static::SOURCE_OF_REQUEST)
            ->addChild('deliveryRoutingRequestEntries')
            ->addChild('deliveryRoutingRequestEntry');
        $this->addCustomerToRoutingRequestNode($routingRequestNode);
        $this->addParcelsToRoutingRequestNode($routingRequestNode);
        $this->addServicesToRoutingRequestNode($routingRequestNode);
        $this->addSenderToRoutingRequestNode($routingRequestNode);
        $routingRequestNode
            ->addChild('expectedDespatchDate', $this->shipment->getCollectionDate()->format('c'))
            ->addChild('countryOfOrigin', $this->shipment->getCollectionAddress()->getISOAlpha2CountryCode());

        return $this->xml;
    }

    protected function addCustomerToRoutingRequestNode(SimpleXMLElement $routingRequestNode): void
    {
        $deliveryAddress = $this->shipment->getDeliveryAddress();
        $customerNode = $routingRequestNode->addChild('customer');
        $this->addAddressToCustomerNode($customerNode, $deliveryAddress);
        $customerNode
            ->addChild('homePhoneNo', $this->sanitiseString($deliveryAddress->getPhoneNumber(), static::MAX_PHONE_LEN))
            ->addChild('email', $this->sanitiseString($deliveryAddress->getEmailAddress(), static::MAX_EMAIL_LEN))
            ->addChild('customerReference1', $this->sanitiseString($this->shipment->getCustomerReference(), static::MAX_REF_LEN))
            ->addChild('deliveryMessage', $this->sanitiseString($this->shipment->getDeliveryInstructions(), static::MAX_INSTRUCT_LEN));
    }

    protected function addAddressToCustomerNode(SimpleXMLElement $customerNode, AddressInterface $deliveryAddress): void
    {
        $customerAddressNode = $customerNode->addChild('address');
        $customerAddressNode
            ->addChild('firstName', $this->sanitiseString($deliveryAddress->getFirstName()))
            ->addChild('lastName', $this->sanitiseString($deliveryAddress->getLastName()))
            ->addChild('streetName', $this->sanitiseString($deliveryAddress->getLine1()))
            ->addChild('addressLine2', $this->sanitiseString($deliveryAddress->getLine2()))
            ->addChild('city', $this->sanitiseString($this->determineCityFromAddress($deliveryAddress)))
            ->addChild('region', $this->sanitiseString($deliveryAddress->getLine4()))
            ->addChild('postCode', $this->sanitiseString($deliveryAddress->getPostCode()))
            ->addChild('countryCode', strtoupper($deliveryAddress->getISOAlpha2CountryCode()));
    }

    protected function addParcelsToRoutingRequestNode(SimpleXMLElement $routingRequestNode): void
    {
        /** @var Package $package */
        foreach ($this->shipment->getPackages() as $package) {
            $parcelNode = $routingRequestNode->addChild('parcel');
            $parcelNode
                ->addChild('weight', $this->convertWeight($package->getWeight()))
                ->addChild('length', $this->convertDimension($package->getLength()))
                ->addChild('width', $this->convertDimension($package->getWidth()))
                ->addChild('depth', $this->convertDimension($package->getHeight()))
                ->addChild('girth', 0)
                ->addChild('combinedDimension', 0)
                ->addChild('volume', 0)
                ->addChild('currency', $this->determineCurrencyOfPackage($package))
                ->addChild('value', $this->calculateValueOfPackage($package))
                ->addChild('dutyPaid', static::DUTY_UNPAID_FLAG);
            $this->addContentsToParcelNode($parcelNode, $package);
        }
    }

    protected function addContentsToParcelNode(SimpleXMLElement $parcelNode, Package $package): void
    {
        $contentsNode = $parcelNode->addChild('contents');
        foreach ($package->getContents() as $content) {
            for ($count = 1; $count <= $content->getQuantity(); $count++) {
                $contentsNode->addChild('content')
                    ->addChild('skuCode', $this->sanitiseString($content->getSku(), static::MAX_SKU_LEN))
                    ->addChild('skuDescription',
                        $this->sanitiseString($content->getName() . "\n" . $content->getDescription(),
                            static::MAX_DESC_LEN))
                    ->addChild('hsCode', $content->getHSCode())
                    ->addChild('value', $this->convertValueToMinorUnits($content->getUnitValue()));
                }
        }
    }

    protected function addServicesToRoutingRequestNode(SimpleXMLElement $routingRequestNode): void
    {
        $servicesNode = $routingRequestNode->addChild('services');
        if ($this->deliveryService->getSpecificDay()) {
            $this->addSpecificDayToServicesNode($servicesNode, $this->deliveryService->getSpecificDay());
        }
        $servicesNode->addChild('nextDay', $this->sanitiseBoolean($this->deliveryService->isNextDay()));
        $servicesNode->addChild('signature', $this->sanitiseBoolean($this->deliveryService->isNextDay()));
    }

    protected function addSpecificDayToServicesNode(SimpleXMLElement $servicesNode, int $specificDay): void
    {
        $servicesNode->addChild('statedDay')
            ->addChild('statedDayIndicator', $specificDay);
    }

    protected function addSenderToRoutingRequestNode(SimpleXMLElement $routingRequestNode): void
    {
        $sendersAddress = $this->shipment->getCollectionAddress();
        $routingRequestNode->addChild('senderAddress')
            ->addChild('addressLine1', $this->sanitiseString($sendersAddress->getLine1()))
            ->addChild('addressLine2', $this->sanitiseString($sendersAddress->getLine2()))
            ->addChild('addressLine3', $this->sanitiseString($sendersAddress->getLine3()))
            ->addChild('addressLine4', $this->sanitiseString($sendersAddress->getLine4()));
    }

    protected function sanitiseString(string $string, int $maxLength = null): string
    {
        return substr($string, 0, $maxLength ?? static::DEFAULT_MAX_LEN);
    }

    protected function sanitiseBoolean(bool $boolean): string
    {
        return $boolean ? 'true' : 'false';
    }

    protected function determineCityFromAddress(AddressInterface $address): string
    {
        if ($address->getLine3()) {
            return $address->getLine3();
        }
        if ($address->getLine4()) {
            return $address->getLine4();
        }
        return $address->getLine2();
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
        return $package->getContents()[0]->getUnitCurrency();
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
}