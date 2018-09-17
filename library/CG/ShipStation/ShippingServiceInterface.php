<?php
namespace CG\ShipStation;

use CG\Channel\Shipping\ServicesInterface as ChannelShippingServiceInterface;
use CG\ShipStation\Messages\CarrierService;

interface ShippingServiceInterface extends ChannelShippingServiceInterface
{
    public function getCarrierService(string $serviceCode): CarrierService;
}