<?php
namespace CG\UkMail\DeliveryService;

use CG\CourierAdapter\DeliveryServiceInterface;

class Service
{
    /** @var DeliveryServiceInterface[] */
    protected $deliveryServices;

    public function __construct(array $servicesConfig = [])
    {

    }

    /**
     * @return DeliveryServiceInterface[]
     */
    public function getDeliveryServices(): array
    {
        return $this->deliveryServices;
    }

    protected function buildServices(array $serviceConfig)
    {
        foreach ($serviceConfig['services'] as $service) {
//            if ($this->serviceOfferingDoesNotExist($serviceConfig['serviceOffering'])) {
//                continue;
//            }
            $config = [];
            $config['reference'] = $service['serviceCode'];
            $config['displayName'] = $service['displayName'];
            $config['domestic'] = $service;
            $this->deliveryServices[$config['reference']] = DeliveryService::fromArray($config);
        }
    }
}