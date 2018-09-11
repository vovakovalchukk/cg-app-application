<?php
namespace CG\ShipStation\ShippingService;

use CG\Order\Shared\ShippableInterface as Order;
use CG\ShipStation\Messages\CarrierService;

class Usps extends Other
{
    /** @var string */
    protected $domesticCountryCode;
    /** @var CarrierService[] */
    protected $services;

    public function __construct(string $domesticCountryCode, array $servicesConfig = [])
    {
        $this->domesticCountryCode = $domesticCountryCode;
        foreach ($servicesConfig as $serviceConfig) {
            $service = CarrierService::fromJson((object)$serviceConfig);
            $this->services[$service->getServiceCode()] = $service;
        }
    }

    public function getShippingServices()
    {
        $serviceOptions = [];
        foreach ($this->services as $service) {
            $serviceOptions[$service->getServiceCode()] = $service->getName();
        }
        return $serviceOptions;
    }

    public function getShippingServicesForOrder(Order $order)
    {
        $serviceOptions = [];
        foreach ($this->services as $service) {
            if (!$this->isServiceApplicableToOrder($service, $order)) {
                continue;
            }
            $serviceOptions[$service->getServiceCode()] = $service->getName();
        }
        return $serviceOptions;
    }

    public function getCarrierService(string $serviceCode): CarrierService
    {
        if (!isset($this->services[$serviceCode])) {
            throw new \InvalidArgumentException($serviceCode . ' not in the list of available shipping services');
        }
        return $this->services[$serviceCode];
    }
}