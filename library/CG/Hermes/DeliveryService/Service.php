<?php
namespace CG\Hermes\DeliveryService;

use CG\CourierAdapter\DeliveryServiceInterface;
use CG\CourierAdapter\Exception\NotFound;
use CG\Hermes\DeliveryService;

class Service
{
    const SERVICE_STANDARD = 'Standard';
    const SERVICE_NEXTDAY = 'NextDay';
    const SERVICE_SUNDAY = 'Sunday';

    protected $deliveryServices = [
        self::SERVICE_STANDARD => 'Standard Service',
        self::SERVICE_NEXTDAY  => 'Next Day',
        self::SERVICE_SUNDAY   => 'Sunday Service',
    ];

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
            throw new NotFound('No hermes services found for reference ' . $reference);
        }
        return $this->deliveryServices[$reference];
    }

    public function getDeliveryServicesForCountry(string $countryCode): array
    {
        $allServices = $this->getDeliveryServices();
        $countryServices = [];
        /** @var DeliveryService $deliveryService */
        foreach ($allServices as $deliveryService) {
            if (!$deliveryService->supportsCountryCode($countryCode)) {
                continue;
            }
            $countryServices[$deliveryService->getReference()] = $deliveryService;
        }
        return $countryServices;
    }
}