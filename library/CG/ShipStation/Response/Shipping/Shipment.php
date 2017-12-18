<?php
namespace CG\ShipStation\Response\Shipping;

use CG\BigCommerce\Request\Time;
use CG\ShipStation\Messages\AddressValidation;
use CG\ShipStation\Messages\Carrier;
use CG\ShipStation\Messages\Package;
use \CG\ShipStation\Messages\Shipment as ShipmentRequest;
use CG\ShipStation\Messages\ShipmentAddress;
use CG\ShipStation\Messages\Timestamp;
use CG\Stdlib\DateTime;

class Shipment
{
    /** @var AddressValidation */
    protected $addressValidation;
    /** @var string */
    protected $shipmentId;
    /** @var Carrier */
    protected $carrier;
    /** @var string */
    protected $serviceCode;
    /** @var ?string */
    protected $externalShipmentId;
    /** @var DateTime */
    protected $shipDate;
    /** @var Time */
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
    /** @var array */
    protected $errors;

    public function __construct(
        AddressValidation $addressValidation,
        string $shipmentId,
        Carrier $carrier,
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
        array $packages,
        array $errors = []
    ) {
        $this->addressValidation = $addressValidation;
        $this->shipmentId = $shipmentId;
        $this->carrier = $carrier;
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
        $this->errors = $errors;
    }

    public static function build($decodedJson): Shipment
    {
        $addressValidation = AddressValidation::build($decodedJson->address_validation);
        $shipDate = new DateTime($decodedJson->ship_date);
        $createdAt = new DateTime($decodedJson->created_at);
        $modifiedAt = new DateTime($decodedJson->modified_at);
        $shipTo = ShipmentAddress::build($decodedJson->ship_to);
        $shipFrom = ShipmentAddress::build($decodedJson->ship_from);
        $returnTo = ShipmentAddress::build($decodedJson->return_to);
        $packages = [];
        foreach ($decodedJson->packages as $packageJson) {
            $packages[] = Package::build($packageJson);
        }
        $errors = [];
        if (isset($decodedJson->errors)) {
            foreach ($decodedJson->errors as $errorJson) {
                $errors[] = $errorJson->message;
            }
        }

        return new static(
            $addressValidation,
            $decodedJson->shipment_id,
            new Carrier($decodedJson->carrier_id),
            $decodedJson->service_code,
            $decodedJson->external_shipment_id,
            $shipDate,
            new Timestamp($createdAt, $modifiedAt),
            $decodedJson->shipment_status,
            $shipTo,
            $shipFrom,
            $decodedJson->warehouse_id,
            $returnTo,
            $decodedJson->confirmation,
            isset($decodedJson->advanced_options) ? (array)$decodedJson->advanced_options : [],
            $decodedJson->insurance_provider,
            $decodedJson->tags ?? [],
            $decodedJson->total_weight->value,
            $decodedJson->total_weight->units,
            $packages,
            $errors
        );
    }

    public function getOrderId(): string
    {
        $externalIdParts = explode(ShipmentRequest::EXTERNAL_ID_SEP, $this->getExternalShipmentId());
        return $externalIdParts[0];
    }

    public function getAddressValidation(): AddressValidation
    {
        return $this->addressValidation;
    }

    public function getShipmentId(): string
    {
        return $this->shipmentId;
    }

    public function getCarrier(): Carrier
    {
        return $this->carrier;
    }

    public function getServiceCode(): string
    {
        return $this->serviceCode;
    }

    public function getExternalShipmentId()
    {
        return $this->externalShipmentId;
    }

    public function getShipDate(): DateTime
    {
        return $this->shipDate;
    }

    public function getTimestamp(): Timestamp
    {
        return $this->timestamp;
    }

    public function getShipmentStatus(): string
    {
        return $this->shipmentStatus;
    }

    public function getShipTo(): ShipmentAddress
    {
        return $this->shipTo;
    }

    public function getShipFrom(): ShipmentAddress
    {
        return $this->shipFrom;
    }

    public function getWarehouseId(): string
    {
        return $this->warehouseId;
    }

    public function getReturnTo(): ShipmentAddress
    {
        return $this->returnTo;
    }

    public function getConfirmation(): string
    {
        return $this->confirmation;
    }

    public function getAdvancedOptions(): array
    {
        return $this->advancedOptions;
    }

    public function getInsuranceProvider(): string
    {
        return $this->insuranceProvider;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function getTotalWeight(): float
    {
        return $this->totalWeight;
    }

    public function getTotalWeightUnit(): string
    {
        return $this->totalWeightUnit;
    }

    public function getPackages(): array
    {
        return $this->packages;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}