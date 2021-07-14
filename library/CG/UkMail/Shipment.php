<?php
namespace CG\UkMail;

use CG\CourierAdapter\Account;
use CG\CourierAdapter\AddressInterface;
use CG\CourierAdapter\DeliveryServiceInterface;
use CG\CourierAdapter\LabelInterface;
use CG\CourierAdapter\ShipmentInterface;

class Shipment implements ShipmentInterface
{

    public static function fromArray(array $array): Shipment
    {
        return new static();
    }

    public function isCancellable()
    {
        // TODO: Implement isCancellable() method.
    }

    public function isAmendable()
    {
        // TODO: Implement isAmendable() method.
    }

    public function getCustomerReference()
    {
        // TODO: Implement getCustomerReference() method.
    }

    public function getCourierReference()
    {
        // TODO: Implement getCourierReference() method.
    }

    public function getAccount()
    {
        // TODO: Implement getAccount() method.
    }

    public function getDeliveryAddress()
    {
        // TODO: Implement getDeliveryAddress() method.
    }

    public function getDeliveryService()
    {
        // TODO: Implement getDeliveryService() method.
    }

    public function getLabels()
    {
        // TODO: Implement getLabels() method.
    }

    public function getTrackingReferences()
    {
        // TODO: Implement getTrackingReferences() method.
    }
}