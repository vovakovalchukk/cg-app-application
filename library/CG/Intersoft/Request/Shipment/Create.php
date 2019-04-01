<?php
namespace CG\Intersoft\RoyalMail\Request\Shipment;

use CG\CourierAdapter\Shipment\SupportedField\DeliveryInstructionsInterface;
use CG\CourierAdapter\Shipment\SupportedField\InsuranceOptionsInterface;
use CG\CourierAdapter\Shipment\SupportedField\SaturdayDeliveryInterface;
use CG\CourierAdapter\Shipment\SupportedField\SignatureRequiredInterface;
use CG\Product\Detail\Entity as ProductDetail;
use CG\Intersoft\RoyalMail\Request\PostAbstract;
use CG\Intersoft\RoyalMail\Response\Shipment\Create as Response;
use CG\Intersoft\RoyalMail\Shipment;
use CG\Intersoft\RoyalMail\Shipment\Package;
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;
use SimpleXMLElement;

class Create extends PostAbstract
{
    const requestNameSpace = 'createShipmentRequest';

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

    protected function toArray(): array
    {
        return [];
    }

    protected function buildXml(): SimpleXMLElement
    {
        $namespace = static::requestNameSpace;
        $xml = new SimpleXMLElement("<{$namespace}></{$namespace}>");
        $xml = $this->addIntegrationHeader($xml);
        $xml = $this->addShipper($xml);
        $xml = $this->addDestination($xml);
        $xml = $this->addShipmentInformation($xml);
        $xml = $this->addItemInformation($xml);
        return $xml;
    }

    protected function toServiceArray(): array
    {
        $deliveryService = $this->shipment->getDeliveryService();
        /** @var Package $firstPackage */
        $firstPackage = $this->shipment->getPackages()[0];
        [$offering, $type] = explode('-', $deliveryService->getReference());
        return [
            'format' => $firstPackage->getType()->getReference(),
            'offering' => $offering,
            'type' => $type,
            'signature' => $this->shipment->isSignatureRequired(),
            'enhancements' => $this->toEnhancementsArray()
        ];
    }

    protected function toEnhancementsArray(): array
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

    protected function toItemsArray(): array
    {
        $items = [];
        /** @var Package $package */
        foreach ($this->shipment->getPackages() as $package) {
            $count = 0;
            foreach ($package->getContents() as $content) {
                $count += $content->getQuantity();
            }

            $items[] = [
                'count' => $count,
                'weight' => [
                    'unitOfMeasure' => static::WEIGHT_UNIT,
                    'value' => $this->convertWeight($package->getWeight())
                ]
            ];
        }
        return $items;
    }

    protected function convertWeight(float $weight): float
    {
        return (new Mass($weight, ProductDetail::UNIT_MASS))->toUnit(static::WEIGHT_UNIT);
    }

    protected function toContactArray(): array
    {
        $deliveryAddress = $this->shipment->getDeliveryAddress();
        return [
            'name' => $deliveryAddress->getFirstName() . ' ' . $deliveryAddress->getLastName(),
            'complementaryName' => $deliveryAddress->getCompanyName(),
            'telephoneNumber' => $deliveryAddress->getPhoneNumber(),
            'email' => $deliveryAddress->getEmailAddress(),
        ];
    }

    protected function toAddressArray(): array
    {
        $deliveryAddress = $this->shipment->getDeliveryAddress();
        return [
            'addressLine1' => $deliveryAddress->getLine1(),
            'addressLine2' => $deliveryAddress->getLine2(),
            'postTown' => $deliveryAddress->getLine3() ?: $deliveryAddress->getLine2() ?: $deliveryAddress->getLine4(),
            'county' => $deliveryAddress->getLine4() ?: $deliveryAddress->getLine3() ?: $deliveryAddress->getLine2(),
            'postCode' => $deliveryAddress->getPostCode(),
            'countryCode' => strtoupper($deliveryAddress->getISOAlpha2CountryCode()),
        ];
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
        $xml->addChild('shipperCompanyName', $this->shipment->getCollectionAddress()->getCompanyName());
        $xml->addChild('shipperAddressLine1', $this->shipment->getCollectionAddress()->getLine1());
        $xml->addChild(
        'shipperCity',
        $this->shipment->getCollectionAddress()->getLine3()
            ?: $this->shipment->getCollectionAddress()->getLine2()
            ?: $this->shipment->getCollectionAddress()->getLine4()
        );
        $xml->addChild('shipperCountryCode', $this->shipment->getCollectionAddress()->getISOAlpha2CountryCode());
        $xml->addChild('shipperPhoneNumber', $this->shipment->getCollectionAddress()->getPhoneNumber());
        $xml->addChild('shipperReference', $this->shipment->getCustomerReference());
        return $xml;
    }

    protected function addDestination(SimpleXMLElement $xml): SimpleXMLElement
    {
        return $xml;
    }

    protected function addShipmentInformation(SimpleXMLElement $xml): SimpleXMLElement
    {
        return $xml;
    }

    protected function addItemInformation(SimpleXMLElement $xml): SimpleXMLElement
    {
        return $xml;
    }
}