<?php
namespace CG\ShipStation\Response\Shipping;

use CG\ShipStation\Entity\Carrier;
use CG\ShipStation\Entity\CarrierService;
use CG\ShipStation\ResponseAbstract;

class CarrierServices extends ResponseAbstract
{
    /** @var  CarrierServiceEntity[] */
    protected $services = [];

    public function __construct(array $services)
    {
        $this->services = $services;
    }

    protected static function build($decodedJson)
    {
        $services = [];
        foreach ($decodedJson->services as $service) {
            $carrier = new Carrier($service->carrier_id, $service->carrier_code);
            $carrierService = new CarrierService(
                $service->service_code,
                $service->name,
                $service->domestic,
                $service->international,
                $service->is_multi_package_supported
            );
            $services[] = new CarrierServiceEntity($carrier, $carrierService);
        }
        return new static($services);
    }

    /**
     * @return CarrierServiceEntity[]
     */
    public function getServices(): array
    {
        return $this->services;
    }
}
