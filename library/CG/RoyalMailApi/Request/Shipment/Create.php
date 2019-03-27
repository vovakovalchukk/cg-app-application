<?php
namespace CG\RoyalMailApi\Request\Shipment;

use CG\CourierAdapter\Shipment\SupportedField\DeliveryInstructionsInterface;
use CG\CourierAdapter\Shipment\SupportedField\InsuranceOptionsInterface;
use CG\CourierAdapter\Shipment\SupportedField\SaturdayDeliveryInterface;
use CG\CourierAdapter\Shipment\SupportedField\SignatureRequiredInterface;
use CG\Product\Detail\Entity as ProductDetail;
use CG\RoyalMailApi\Request\PostAbstract;
use CG\RoyalMailApi\Response\Shipment\Create as Response;
use CG\RoyalMailApi\Shipment;
use CG\RoyalMailApi\Shipment\Package;
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;

abstract class Create extends PostAbstract
{
    const SHIPMENT_TYPE = 'Delivery';
    const DATE_FORMAT = 'Y-m-d';
    const WEIGHT_UNIT = 'g';
    const ENHANCEMENT_SIGNATURE = 12;
    const ENHANCEMENT_SATURDAY = 24;

    /** @var Shipment */
    protected $shipment;

    public function __construct(Shipment $shipment)
    {
        $this->shipment = $shipment;
    }

    public function getUri(): string
    {
        return 'shipments';
    }

    public function getResponseClass(): string
    {
        return Response::class;
    }

    protected function toArray(): array
    {
        return [
            'shipmentType' => static::SHIPMENT_TYPE,
            'service' => $this->toServiceArray(),
            'shippingDate' => $this->shipment->getCollectionDate()->format(static::DATE_FORMAT),
            'items' => $this->toItemsArray(),
            'recipientContact' => $this->toContactArray(),
            'recipientAddress' => $this->toAddressArray(),
            'senderReference' => $this->shipment->getCustomerReference(),
            'safePlace' => $this->getSafePlace(),
        ];
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
}