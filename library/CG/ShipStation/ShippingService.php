<?php
namespace CG\ShipStation;

use CG\Channel\Shipping\ServicesInterface as ShippingServiceInterface;
use CG\Order\Shared\ShippableInterface as Order;

class ShippingService implements ShippingServiceInterface
{
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function getShippingServices()
    {
        // TODO: Implement getShippingServices() method.
    }

    public function getShippingServicesForOrder(Order $order)
    {
        // TODO: Implement getShippingServicesForOrder() method.
    }

    public function doesServiceHaveOptions($service)
    {
        return false;
    }

    public function getOptionsForService($service, $selected = null)
    {
        return [];
    }
}
