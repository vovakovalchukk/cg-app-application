<?php
namespace CG\ShipStation\Response\Shipping;

use CG\ShipStation\ResponseAbstract;
use CG\ShipStation\Response\Shipping\CarrierServiceEntity as CarrierService;

class CarrierServices extends ResponseAbstract
{
    /** @var  CarrierService[] */
    protected $services = [];

    protected function build($decodedJson)
    {
        foreach ($decodedJson->services as $service) {
            $this->services[] = new CarrierService(
                $service->carrier_id,
                $service->carrier_code,
                $service->service_code,
                $service->name,
                $service->domectic,
                $service->international,
                $service->is_multi_package_supported
            );
        }
        return $this;
    }

    /**
     * @return CarrierService[]
     */
    public function getServices(): array
    {
        return $this->services;
    }
}
