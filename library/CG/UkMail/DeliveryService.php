<?php
namespace CG\UkMail;

use CG\CourierAdapter\DeliveryServiceInterface;
use CG\CourierAdapter\OperationFailed;
use CG\CourierAdapter\ShipmentInterface;
use CG\CourierAdapter\UserError;

class DeliveryService implements DeliveryServiceInterface
{
    /** @var string */
    protected $reference;
    /** @var string */
    protected $displayName;
    /** @var bool */
    protected $domestic;

    public function __construct(string $reference, string $displayName, bool $domestic)
    {
        $this->reference = $reference;
        $this->displayName = $displayName;
        $this->domestic = $domestic;
    }

    public function getDisplayName()
    {
        return $this->displayName;
    }

    public function getDescription()
    {
        return $this->displayName;
    }

    public function getReference()
    {
        return $this->reference;
    }

    public function isISOAlpha2CountryCodeSupported($isoAlpha2CountryCode)
    {
        return true;
    }

    public function getShipmentClass()
    {
        return Shipment::class;
    }

    public function createShipment(array $shipmentDetails)
    {
        $shipmentDetails['deliveryService'] = $this;
        return Shipment::fromArray($shipmentDetails);
    }
}