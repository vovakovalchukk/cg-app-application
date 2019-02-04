<?php
namespace CG\ShipStation\Response\Shipping;

use CG\ShipStation\Messages\Carrier;
use CG\ShipStation\Messages\Downloadable;
use CG\ShipStation\ResponseAbstract;
use CG\Stdlib\DateTime;

class Label extends ResponseAbstract
{
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_ERROR = 'error';
    const STATUS_VOIDED = 'voided';

    const CARRIER_ERROR_PREFIX = '/^A shipping carrier reported an error when processing your request. Carrier ID: [^,]+, Carrier: [^\.]+. ?/';

    /** @var string */
    protected $labelId;
    /** @var string */
    protected $status;
    /** @var string */
    protected $shipmentId;
    /** @var DateTime */
    protected $shipDate;
    /** @var DateTime */
    protected $createdAt;
    /** @var float */
    protected $shipmentCost;
    /** @var string */
    protected $shipmentCostCurrency;
    /** @var float */
    protected $insuranceCost;
    /** @var string */
    protected $insuranceCostCurrency;
    /** @var string */
    protected $trackingNumber;
    /** @var string */
    protected $carrierReferenceNumber;
    /** @var bool */
    protected $returnLabel;
    /** @var bool */
    protected $international;
    /** @var string */
    protected $batchId;
    /** @var Carrier */
    protected $carrier;
    /** @var string */
    protected $serviceCode;
    /** @var string */
    protected $packageCode;
    /** @var bool */
    protected $voided;
    /** @var DateTime */
    protected $voidedAt;
    /** @var string */
    protected $labelFormat;
    /** @var string */
    protected $labelLayout;
    /** @var bool */
    protected $trackable;
    /** @var string */
    protected $trackingStatus;
    /** @var Downloadable */
    protected $labelDownload;
    /** @var Downloadable */
    protected $formDownload;
    /** @var Downloadable */
    protected $insuranceClaim;
    /** @var array */
    protected $errors;

    public function __construct(
        ?string $labelId,
        ?string $status,
        ?string $shipmentId,
        ?DateTime $shipDate,
        ?DateTime $createdAt,
        ?float $shipmentCost,
        ?string $shipmentCostCurrency,
        ?float $insuranceCost,
        ?string $insuranceCostCurrency,
        ?string $trackingNumber,
        ?string $carrierReferenceNumber,
        ?bool $returnLabel,
        ?bool $international,
        ?string $batchId,
        ?Carrier $carrier,
        ?string $serviceCode,
        ?string $packageCode,
        ?bool $voided,
        ?DateTime $voidedAt,
        ?string $labelFormat,
        ?string $labelLayout,
        ?bool $trackable,
        ?string $trackingStatus,
        ?Downloadable $labelDownload,
        ?Downloadable $formDownload,
        ?Downloadable $insuranceClaim,
        array $errors = []
    ) {
        $this->labelId = $labelId;
        $this->status = $status;
        $this->shipmentId = $shipmentId;
        $this->shipDate = $shipDate;
        $this->createdAt = $createdAt;
        $this->shipmentCost = $shipmentCost;
        $this->shipmentCostCurrency = $shipmentCostCurrency;
        $this->insuranceCost = $insuranceCost;
        $this->insuranceCostCurrency = $insuranceCostCurrency;
        $this->trackingNumber = $trackingNumber;
        $this->carrierReferenceNumber = $carrierReferenceNumber;
        $this->returnLabel = $returnLabel;
        $this->international = $international;
        $this->batchId = $batchId;
        $this->carrier = $carrier;
        $this->serviceCode = $serviceCode;
        $this->packageCode = $packageCode;
        $this->voided = $voided;
        $this->voidedAt = $voidedAt;
        $this->labelFormat = $labelFormat;
        $this->labelLayout = $labelLayout;
        $this->trackable = $trackable;
        $this->trackingStatus = $trackingStatus;
        $this->labelDownload = $labelDownload;
        $this->formDownload = $formDownload;
        $this->insuranceClaim = $insuranceClaim;
        $this->errors = $errors;
    }

    protected static function build($decodedJson)
    {
        $errors = [];
        if (isset($decodedJson->errors)) {
            foreach ($decodedJson->errors as $errorJson) {
                $errors[] = static::sanitiseError($errorJson->message);
            }
        }

        return new static(
            $decodedJson->label_id ?? null,
            $decodedJson->status ?? null,
            $decodedJson->shipment_id ?? null,
            isset($decodedJson->ship_date) ? new DateTime($decodedJson->ship_date) : null,
            isset($decodedJson->created_at) ? new DateTime($decodedJson->created_at) : null,
            $decodedJson->shipment_cost->amount ?? null,
            $decodedJson->shipment_cost->currency ?? null,
            $decodedJson->insurance_cost->amount ?? null,
            $decodedJson->insurance_cost->currency ?? null,
            $decodedJson->tracking_number ?? null,
            $decodedJson->carrier_response_details->reference_number ?? null,
            $decodedJson->is_return_label ?? null,
            $decodedJson->is_international ?? null,
            $decodedJson->batch_id ?? null,
            isset($decodedJson->carrier_id) ? new Carrier($decodedJson->carrier_id, $decodedJson->carrier_code ?? '') : null,
            $decodedJson->service_code ?? null,
            $decodedJson->package_code ?? null,
            $decodedJson->voided ?? null,
            isset($decodedJson->voided_at) ? new DateTime($decodedJson->voided_at) : null,
            $decodedJson->label_format ?? null,
            $decodedJson->label_layout ?? null,
            $decodedJson->trackable ?? null,
            $decodedJson->tracking_status ?? null,
            isset($decodedJson->label_download) ? Downloadable::build($decodedJson->label_download) : null,
            isset($decodedJson->form_download) ? Downloadable::build($decodedJson->form_download) : null,
            isset($decodedJson->insurance_claim) ? Downloadable::build($decodedJson->insurance_claim) : null,
            $errors
        );
    }

    public static function sanitiseError(string $error): string
    {
        // There's often some fluff at the start of the error that we can strip out
        return preg_replace(static::CARRIER_ERROR_PREFIX, '', $error);
    }

    public static function getActiveStatuses()
    {
        return [
            static::STATUS_PROCESSING, static::STATUS_COMPLETED
        ];
    }

    public function getLabelId(): string
    {
        return $this->labelId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getShipmentId(): string
    {
        return $this->shipmentId;
    }

    public function getShipDate(): DateTime
    {
        return $this->shipDate;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getShipmentCost(): float
    {
        return $this->shipmentCost;
    }

    public function getShipmentCostCurrency(): string
    {
        return $this->shipmentCostCurrency;
    }

    public function getInsuranceCost(): float
    {
        return $this->insuranceCost;
    }

    public function getInsuranceCostCurrency(): string
    {
        return $this->insuranceCostCurrency;
    }

    public function getTrackingNumber(): ?string
    {
        return $this->trackingNumber;
    }

    public function getCarrierReferenceNumber(): ?string
    {
        return $this->carrierReferenceNumber;
    }

    public function isReturnLabel(): bool
    {
        return $this->returnLabel;
    }

    public function isInternational(): bool
    {
        return $this->international;
    }

    public function getBatchId(): string
    {
        return $this->batchId;
    }

    public function getCarrier(): Carrier
    {
        return $this->carrier;
    }

    public function getServiceCode(): string
    {
        return $this->serviceCode;
    }

    public function getPackageCode(): string
    {
        return $this->packageCode;
    }

    public function isVoided(): bool
    {
        return $this->voided;
    }

    public function getVoidedAt(): ?DateTime
    {
        return $this->voidedAt;
    }

    public function getLabelFormat(): string
    {
        return $this->labelFormat;
    }

    public function getLabelLayout(): string
    {
        return $this->labelLayout;
    }

    public function isTrackable(): bool
    {
        return $this->trackable;
    }

    public function getTrackingStatus(): string
    {
        return $this->trackingStatus;
    }

    public function getLabelDownload(): ?Downloadable
    {
        return $this->labelDownload;
    }

    public function getFormDownload(): ?Downloadable
    {
        return $this->formDownload;
    }

    public function getInsuranceClaim(): ?Downloadable
    {
        return $this->insuranceClaim;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}