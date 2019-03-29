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

abstract class Create extends PostAbstract
{
    /** @var Shipment */
    protected $shipment;

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

    protected function toArray(): array
    {
        return [
            'shipperCompanyName',
            'shipperAddressLine1' => $this->shipment->getCollectionAddress()->getLine1(),
            'shipperAddressLine2' => $this->shipment->getCollectionAddress()->getLine2(),
            'shipperAddressLine3' => $this->shipment->getCollectionAddress()->getLine3(),
            'shipperAddressLine3' => $this->shipment->getCollectionAddress()->getLine3(),
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