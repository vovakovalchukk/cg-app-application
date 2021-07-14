<?php
namespace CG\UkMail\DeliveryService;

use CG\CourierAdapter\DeliveryServiceInterface;
use CG\UkMail\DeliveryService;

class Service
{
    /** @var DeliveryServiceInterface[] */
    protected $deliveryServices;

    public function __construct(array $servicesConfig = [])
    {
        $this->buildServices($servicesConfig);
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
            throw new NotFound('No UkMail services found for reference ' . $reference);
        }
        return $this->deliveryServices[$reference];
    }

    public function getDeliveryServicesForCountry(): array
    {
        $allServices = $this->getDeliveryServices();
        $countryServices = [];
        /** @var DeliveryService $deliveryService */
        foreach ($allServices as $deliveryService) {
            if ($deliveryService->isDomesticService()) {
                continue;
            }
            $countryServices[$deliveryService->getReference()] = $deliveryService;
        }
        return $countryServices;
    }

    public function getDomesticDeliveryServices(): array
    {
        $allServices = $this->getDeliveryServices();
        $domesticServices = [];
        /** @var DeliveryService $deliveryService */
        foreach ($allServices as $deliveryService) {
            if (!$deliveryService->isDomesticService()) {
                continue;
            }
            $domesticServices[$deliveryService->getReference()] = $deliveryService;
        }
        return $domesticServices;
    }

    protected function buildServices(array $servicesConfig)
    {
        foreach ($servicesConfig['services'] as $service) {
            $config = [];
            $config['reference'] = $service['serviceCode'];
            $config['displayName'] = $service['displayName'];
            $config['domestic'] = $service['domestic'];
            $this->deliveryServices[$config['reference']] = DeliveryService::fromArray($config);
        }
    }
}