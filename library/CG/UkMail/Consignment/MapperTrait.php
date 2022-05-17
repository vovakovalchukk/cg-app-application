<?php
namespace CG\UkMail\Consignment;

use CG\UkMail\Shipment;

trait MapperTrait
{
    /**
     * In case of linked orders which $shipment->getCustomerReference() returns all linked orders' ids separated by comma
     * We will return first order's id instead cut string in middle
     * In case of string extending 20 characters as it is order id to be sure that is unambiguous we want to return last 20 characters
     */
    protected function getCustomerReference(Shipment $shipment): string
    {
        $result = explode(',', $shipment->getCustomerReference());
        return substr($result[0], -20);
    }
}