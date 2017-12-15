<?php
namespace CG\ShipStation\Request\Shipping;

use CG\ShipStation\Messages\Shipment;
use CG\ShipStation\Messages\ShipmentAddress;
use CG\ShipStation\RequestAbstract;
use CG\ShipStation\Response\Shipping\Shipments as Response;

class Shipments extends RequestAbstract
{
    const METHOD = 'POST';
    const URI = '/shipments';

    /** @var Shipment[] */
    protected $shipments;

    public function __construct(Shipment ...$shipments)
    {
        $this->shipments = $shipments;
    }

    public function toArray(): array
    {
        return ['shipments' => $this->getShipmentsArray()];
    }

    protected function getShipmentsArray(): array
    {
        $shipments = [];
        foreach ($this->shipments as $shipment) {
            $shipments[] = [
                'service_code' => $shipment->getServiceCode(),
                'ship_to' => $this->getShipmentAddressArray($shipment->getShipTo()),
                'warehouse_id' => $shipment->getWarehouseId(),
                'packages' => $this->getPackagesArray($shipment),
            ];
        }
        return $shipments;
    }

    protected function getShipmentAddressArray(ShipmentAddress $shipmentAddress)
    {
        return [
            'name' => $shipmentAddress->getName(),
            'phone' => $shipmentAddress->getPhone(),
            'company_name' => $shipmentAddress->getCompanyName(),
            'address_line1' => $shipmentAddress->getAddressLine1(),
            'address_line2' => $shipmentAddress->getAddressLine2(),
            'city_locality' => $shipmentAddress->getCityLocality(),
            'state_province' => $shipmentAddress->getStateProvince(),
            'postal_code' => $shipmentAddress->getPostalCode(),
            'country_code' => $shipmentAddress->getCountryCode(),
            'address_residential_indicator' => $this->getAddressResidentialIndicatorString($shipmentAddress),
        ];
    }

    protected function getAddressResidentialIndicatorString(ShipmentAddress $shipmentAddress)
    {
        $addressResidentialIndicator = $shipmentAddress->isAddressResidentialIndicator();
        if ($addressResidentialIndicator === null) {
            return 'unknown';
        }
        return $addressResidentialIndicator ? 'yes': 'no';
    }

    protected function getPackagesArray(Shipment $shipment): array
    {
        $packages = [];
        foreach ($shipment->getPackages() as $package) {
            $packages[] = [
                'weight' => [
                    'value' => $package->getWeight(),
                    'unit' => $package->getWeightUnit(),
                ],
                'dimensions' => [
                    'length' => $package->getLength(),
                    'width' => $package->getWidth(),
                    'height' => $package->getHeight(),
                    'unit' => $package->getDimensionsUnit(),
                ],
                'insured_value' => [
                    'amount' => $package->getInsuredValue(),
                    'currency' => $package->getInsuredCurrency(),
                ],
            ];
        }
        return $packages;
    }

    public function getResponseClass(): string
    {
        return Response::class;
    }
}