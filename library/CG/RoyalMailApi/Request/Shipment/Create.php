<?php
namespace CG\RoyalMailApi\Request\Shipment;

use CG\Product\Detail\Entity as ProductDetail;
use CG\RoyalMailApi\Request\PostAbstract;
use CG\RoyalMailApi\Response\Shipment\Create as Response;
use CG\RoyalMailApi\Shipment;
use CG\RoyalMailApi\Shipment\Package;
use PhpUnitsOfMeasure\PhysicalQuantity\Length;
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;

class Create extends PostAbstract
{
    const SHIPMENT_TYPE = 'Delivery';
    const DATE_FORMAT = 'Y-m-d';
    const WEIGHT_UNIT = 'g';

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
        $deliveryService = $this->shipment->getDeliveryService();
        /** @var Package $firstPackage */
        $firstPackage = $this->shipment->getPackages()[0];
        [$offering, $type] = explode('-', $deliveryService->getReference());
        return [
            'shipmentType' => static::SHIPMENT_TYPE,
            'service' => [
                'format' => $firstPackage->getType(),
                'offering' => $offering,
                'type' => $type,
                'signature' => $this->shipment->isSignatureRequired(),
                'enhancements' => null, // TODO
            ],
            'shippingDate' => $this->shipment->getCollectionDate()->format(static::DATE_FORMAT),
            'items' => $this->toItemsArray(),
            'recipientContact' => $this->toContactArray(),
            // TODO
        ];
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
        //TODO
    }
}