<?php
namespace CG\Intersoft\RoyalMail\DeliveryService;

use CG\CourierAdapter\DeliveryServiceInterface;
use CG\CourierAdapter\Exception\NotFound;
use CG\Intersoft\RoyalMail\DeliveryService;

class Service
{
    /** @var DeliveryServiceInterface[] */
    protected $deliveryServices;
    /** @var array */
    protected $serviceTypes;
    /** @var array */
    protected $serviceOfferings;
    /** @var array */
    protected $serviceFormats;

    public function __construct(array $servicesConfig = [])
    {
        $this->serviceTypes = $servicesConfig['serviceTypes'] ?? [];
        $this->serviceOfferings = $servicesConfig['serviceOfferings'] ?? [];
        $this->serviceFormats = $servicesConfig['serviceFormats'] ?? [];
        foreach ($servicesConfig['services'] as $serviceConfig) {
            $this->buildServices($serviceConfig);
        }
    }

    /**
     * @return DeliveryServiceInterface[]
     */
    public function getDeliveryServices(): array
    {
        return $this->deliveryServices;
    }

    public function getDeliveryServiceByReference(string $reference): DeliveryServiceInterface
    {
        if (!isset($this->deliveryServices[$reference])) {
            throw new NotFound('No royal mail services found for reference ' . $reference);
        }
        return $this->deliveryServices[$reference];
    }

    public function getDeliveryServicesForCountry(string $countryCode): array
    {
        $allServices = $this->getDeliveryServices();
        $countryServices = [];
        /** @var DeliveryService $deliveryService */
        foreach ($allServices as $deliveryService) {
            if (!$deliveryService->isISOAlpha2CountryCodeSupported($countryCode)) {
                continue;
            }
            $countryServices[$deliveryService->getReference()] = $deliveryService;
        }
        return $countryServices;
    }

    protected function buildServices(array $serviceConfig)
    {
        foreach ($serviceConfig['serviceTypes'] as $type) {
            if ($this->serviceOfferingDoesNotExist($serviceConfig['serviceOffering'])) {
                continue;
            }
            $config = [];
            $config['reference'] = $serviceConfig['serviceOffering'];
            $config['displayName'] = $this->getServiceOfferings()[$serviceConfig['serviceOffering']]['displayName'] . ' - ' . $this->getServiceTypes()[$type]['description'];
            $config['serviceType'] = $type;
            $config['shipmentClass'] = $serviceConfig['shipmentClass'];
            $this->deliveryServices[$config['reference']] = DeliveryService::fromArray($config);
        }
    }

    public function getServiceTypes(): array
    {
        return $this->serviceTypes;
    }

    public function getServiceOfferings(): array
    {
        return $this->serviceOfferings;
    }

    public function getServiceFormats(): array
    {
        return $this->serviceFormats;
    }

    protected function serviceOfferingDoesNotExist(string $serviceOffering): bool
    {
        return !isset($this->getServiceOfferings()[$serviceOffering]);
    }
}