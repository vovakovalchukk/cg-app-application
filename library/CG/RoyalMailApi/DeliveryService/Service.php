<?php
namespace CG\RoyalMailApi\DeliveryService;

use CG\CourierAdapter\DeliveryServiceInterface;
use CG\CourierAdapter\Exception\NotFound;
use CG\RoyalMailApi\DeliveryService;

class Service
{
    /** @var DeliveryServiceInterface[] */
    protected $deliveryServices;

    public function __construct(array $servicesConfig = [], array $defaultConfig = [])
    {
        $this->deliveryServices = [];
        foreach ($servicesConfig as $reference => $config) {
            $config['reference'] = $reference;
            $mergedConfig = array_merge($defaultConfig, $config);
            $this->deliveryServices[$reference] = DeliveryService::fromArray($mergedConfig);
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
}