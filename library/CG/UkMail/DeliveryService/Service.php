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
}