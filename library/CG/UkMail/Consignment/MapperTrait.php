<?php
namespace CG\UkMail\Consignment;

use CG\UkMail\Shipment;

trait MapperTrait
{
    protected function getCustomerReference(Shipment $shipment): string
    {
        $result = explode(',', $shipment->getCustomerReference());
        return (strlen($result[0]) > 20) ? substr($result[0], -20) : $result[0];
    }
}