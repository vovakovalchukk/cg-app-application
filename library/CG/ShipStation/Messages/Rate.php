<?php
namespace CG\ShipStation\Messages;

class Rate
{
    /** @var string */
    protected $rateId;
    /** @var string */
    protected $rateType;
    /** @var string */
    protected $carrierId;
    /** @var CurrencyAmount */
    protected $shippingAmount;
    /** @var CurrencyAmount */
    protected $insuranceAmount;
    /** @var CurrencyAmount */
    protected $confirmationAmount;
    /** @var CurrencyAmount */
    protected $otherAmount;
    /** @var string|null */
    protected $zone;
    /** @var string|null */
    protected $packageType;
    /** @var int */
    protected $deliveryDays;
    /** @var bool */
    protected $guaranteedService;
    /** @var string */
    protected $estimatedDeliveryDate;
    /** @var string|null */
    protected $carrierDeliveryDays;
    /** @var string */
    protected $shipDate;
    /** @var bool */
    protected $negotiatedRate;
    /** @var string */
    protected $serviceType;
    /** @var string */
    protected $serviceCode;
    /** @var bool */
    protected $trackable;
    /** @var string */
    protected $validationStatus;
    /** @var array */
    protected $warningMessages;
    /** @var array */
    protected $errorMessages;
    /** @var string */
    protected $carrierCode;
    /** @var string */
    protected $carrierNickname;
    /** @var string */
    protected $carrierFriendlyName;

    public function __construct(
        string $rateId,
        string $rateType,
        string $carrierId,
        CurrencyAmount $shippingAmount,
        CurrencyAmount $insuranceAmount,
        CurrencyAmount $confirmationAmount,
        CurrencyAmount $otherAmount,
        ?string $zone,
        ?string $packageType,
        int $deliveryDays,
        bool $guaranteedService,
        string $estimatedDeliveryDate,
        ?string $carrierDeliveryDays,
        string $shipDate,
        bool $negotiatedRate,
        string $serviceType,
        string $serviceCode,
        bool $trackable,
        string $validationStatus,
        array $warningMessages,
        array $errorMessages,
        string $carrierCode,
        string $carrierNickname,
        string $carrierFriendlyName
    ) {
        $this->rateId = $rateId;
        $this->rateType = $rateType;
        $this->carrierId = $carrierId;
        $this->shippingAmount = $shippingAmount;
        $this->insuranceAmount = $insuranceAmount;
        $this->confirmationAmount = $confirmationAmount;
        $this->otherAmount = $otherAmount;
        $this->zone = $zone;
        $this->packageType = $packageType;
        $this->deliveryDays = $deliveryDays;
        $this->guaranteedService = $guaranteedService;
        $this->estimatedDeliveryDate = $estimatedDeliveryDate;
        $this->carrierDeliveryDays = $carrierDeliveryDays;
        $this->shipDate = $shipDate;
        $this->negotiatedRate = $negotiatedRate;
        $this->serviceType = $serviceType;
        $this->serviceCode = $serviceCode;
        $this->trackable = $trackable;
        $this->validationStatus = $validationStatus;
        $this->warningMessages = $warningMessages;
        $this->errorMessages = $errorMessages;
        $this->carrierCode = $carrierCode;
        $this->carrierNickname = $carrierNickname;
        $this->carrierFriendlyName = $carrierFriendlyName;
    }

    public static function build(\stdClass $decodedJson): Rate
    {
        return new static(
            $decodedJson->rate_id,
            $decodedJson->rate_type,
            $decodedJson->carrier_id,
            CurrencyAmount::build($decodedJson->shipping_amount),
            CurrencyAmount::build($decodedJson->insurance_amount),
            CurrencyAmount::build($decodedJson->confirmation_amount),
            CurrencyAmount::build($decodedJson->other_amount),
            $decodedJson->zone ?? null,
            $decodedJson->package_type ?? null,
            $decodedJson->delivery_days,
            $decodedJson->guaranteed_service,
            $decodedJson->estimated_delivery_date,
            $decodedJson->carrier_delivery_days ?? null,
            $decodedJson->ship_date,
            $decodedJson->negotiated_rate,
            $decodedJson->service_type,
            $decodedJson->service_code,
            $decodedJson->trackable,
            $decodedJson->validation_status,
            $decodedJson->warning_messages,
            $decodedJson->error_messages,
            $decodedJson->carrier_code,
            $decodedJson->carrier_nickname,
            $decodedJson->carrier_friendly_name
        );
    }

    public function getRateId(): string
    {
        return $this->rateId;
    }

    public function getRateType(): string
    {
        return $this->rateType;
    }

    public function getCarrierId(): string
    {
        return $this->carrierId;
    }

    public function getShippingAmount(): CurrencyAmount
    {
        return $this->shippingAmount;
    }

    public function getInsuranceAmount(): CurrencyAmount
    {
        return $this->insuranceAmount;
    }

    public function getConfirmationAmount(): CurrencyAmount
    {
        return $this->confirmationAmount;
    }

    public function getOtherAmount(): CurrencyAmount
    {
        return $this->otherAmount;
    }

    public function getZone(): ?string
    {
        return $this->zone;
    }

    public function getPackageType(): ?string
    {
        return $this->packageType;
    }

    public function getDeliveryDays(): int
    {
        return $this->deliveryDays;
    }

    public function isGuaranteedService(): bool
    {
        return $this->guaranteedService;
    }

    public function getEstimatedDeliveryDate(): string
    {
        return $this->estimatedDeliveryDate;
    }

    public function getCarrierDeliveryDays(): ?string
    {
        return $this->carrierDeliveryDays;
    }

    public function getShipDate(): string
    {
        return $this->shipDate;
    }

    public function isNegotiatedRate(): bool
    {
        return $this->negotiatedRate;
    }

    public function getServiceType(): string
    {
        return $this->serviceType;
    }

    public function getServiceCode(): string
    {
        return $this->serviceCode;
    }

    public function isTrackable(): bool
    {
        return $this->trackable;
    }

    public function getValidationStatus(): string
    {
        return $this->validationStatus;
    }

    public function getWarningMessages(): array
    {
        return $this->warningMessages;
    }

    public function getErrorMessages(): array
    {
        return $this->errorMessages;
    }

    public function getCarrierCode(): string
    {
        return $this->carrierCode;
    }

    public function getCarrierNickname(): string
    {
        return $this->carrierNickname;
    }

    public function getCarrierFriendlyName(): string
    {
        return $this->carrierFriendlyName;
    }
}