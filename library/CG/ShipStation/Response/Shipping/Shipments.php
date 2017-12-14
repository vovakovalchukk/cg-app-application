<?php
namespace CG\ShipStation\Response\Shipping;

use CG\ShipStation\Entity\AddressValidation;
use CG\ShipStation\Entity\Package;
use CG\ShipStation\Entity\ShipmentAddress;
use CG\ShipStation\Entity\Timestamp;
use CG\ShipStation\ResponseAbstract;
use CG\Stdlib\DateTime;

class Shipments extends ResponseAbstract
{
    /** @var AddressValidation */
    protected $addressValidation;
    /** @var string */
    protected $shipmentId;
    /** @var string */
    protected $carrierId;
    /** @var string */
    protected $serviceCode;
    /** @var ?string */
    protected $externalShipmentId;
    /** @var DateTime */
    protected $shipDate;
    /** @var Timestamp */
    protected $timestamp;
    /** @var string */
    protected $shipmentStatus;
    /** @var ShipmentAddress */
    protected $shipTo;
    /** @var ShipmentAddress */
    protected $shipFrom;
    /** @var string */
    protected $warehouseId;
    /** @var ShipmentAddress */
    protected $returnTo;
    /** @var string */
    protected $confirmation;
    /** @var array */
    protected $advancedOptions;
    /** @var string */
    protected $insuranceProvider;
    /** @var array */
    protected $tags;
    /** @var float */
    protected $totalWeight;
    /** @var string */
    protected $totalWeightUnit;
    /** @var Package[] */
    protected $packages;

    public function __construct(
        AddressValidation $addressValidation,
        string $shipmentId,
        string $carrierId,
        string $serviceCode,
        $externalShipmentId,
        DateTime $shipDate,
        Timestamp $timestamp,
        string $shipmentStatus,
        ShipmentAddress $shipTo,
        ShipmentAddress $shipFrom,
        string $warehouseId,
        ShipmentAddress $returnTo,
        string $confirmation,
        array $advancedOptions,
        string $insuranceProvider,
        array $tags,
        float $totalWeight,
        string $totalWeightUnit,
        array $packages
    ) {
        $this->addressValidation = $addressValidation;
        $this->shipmentId = $shipmentId;
        $this->carrierId = $carrierId;
        $this->serviceCode = $serviceCode;
        $this->externalShipmentId = $externalShipmentId;
        $this->shipDate = $shipDate;
        $this->timestamp = $timestamp;
        $this->shipmentStatus = $shipmentStatus;
        $this->shipTo = $shipTo;
        $this->shipFrom = $shipFrom;
        $this->warehouseId = $warehouseId;
        $this->returnTo = $returnTo;
        $this->confirmation = $confirmation;
        $this->advancedOptions = $advancedOptions;
        $this->insuranceProvider = $insuranceProvider;
        $this->tags = $tags;
        $this->totalWeight = $totalWeight;
        $this->totalWeightUnit = $totalWeightUnit;
        $this->packages = $packages;
    }

    protected static function build($decodedJson)
    {
        return new static();
    }
}