<?php
namespace CG\UkMail\DeliveryService;

use CG\CourierAdapter\DeliveryServiceInterface;
use CG\UkMail\DeliveryService;

class Service
{
    protected const COUNTRY_CODE_GB = 'GB';

    /** @var DeliveryServiceInterface[] */
    protected $deliveryServices;

    protected $domesticServices = [];
    protected $internationalServices = [];

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

    public function getDeliveryServicesForCountry(string $countryCode): array
    {
        if ($countryCode == static::COUNTRY_CODE_GB) {
            return $this->domesticServices;
        }

        return $this->internationalServices;
    }

    protected function buildServices(array $servicesConfig): void
    {
        foreach ($servicesConfig['services'] as $service) {
            $config = [];
            $config['reference'] = $service['serviceCode'];
            $config['displayName'] = $service['displayName'];
            $config['domestic'] = $service['domestic'];
            $this->deliveryServices[$config['reference']] = DeliveryService::fromArray($config);

            if ($this->deliveryServices[$config['reference']]->isDomesticService()) {
                $this->domesticServices[$config['reference']] = $this->deliveryServices[$config['reference']];
                continue;
            }

            $this->internationalServices[$config['reference']] = $this->deliveryServices[$config['reference']];
        }
    }
}