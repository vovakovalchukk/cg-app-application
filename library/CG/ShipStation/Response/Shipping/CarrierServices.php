<?php
namespace CG\ShipStation\Response\Shipping;

use CG\ShipStation\Entity\CarrierService;
use CG\ShipStation\ResponseAbstract;

class CarrierServices extends ResponseAbstract
{
    /** @var  CarrierService[] */
    protected $services = [];

    public function __construct(array $services)
    {
        $this->services = $services;
    }

    protected static function build($decodedJson)
    {
        $services = [];
        foreach ($decodedJson->services as $service) {
            $services[] = new CarrierService(
                $service->service_code,
                $service->name,
                $service->domestic,
                $service->international,
                $service->is_multi_package_supported
            );
        }
        return new static($services);
    }

    /**
     * @return CarrierService[]
     */
    public function getServices(): array
    {
        return $this->services;
    }
}
