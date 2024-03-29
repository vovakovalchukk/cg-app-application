<?php
namespace CG\Hermes;

use CG\CourierAdapter\Account;
use CG\CourierAdapter\AddressInterface;
use CG\CourierAdapter\DeliveryServiceInterface;
use CG\CourierAdapter\LabelInterface;
use CG\CourierAdapter\PackageInterface;
use CG\CourierAdapter\Shipment\SupportedField\DeliveredDutyInterface;
use CG\CourierAdapter\Shipment\SupportedField\EoriNumberInterface;
use CG\CourierAdapter\Shipment\SupportedField\IossNumberInterface;
use CG\CourierAdapter\ShipmentInterface;
use CG\CourierAdapter\Shipment\SupportedField\CollectionAddressInterface;
use CG\CourierAdapter\Shipment\SupportedField\CollectionDateInterface;
use CG\CourierAdapter\Shipment\SupportedField\DeliveryInstructionsInterface;
use CG\CourierAdapter\Shipment\SupportedField\PackagesInterface;
use CG\CourierAdapter\Shipment\SupportedField\SignatureRequiredInterface;
use CG\Hermes\Shipment\Package;
use DateTime;

class Shipment implements
    ShipmentInterface,
    CollectionAddressInterface,
    DeliveryInstructionsInterface,
    CollectionDateInterface,
    PackagesInterface,
    SignatureRequiredInterface,
    DeliveredDutyInterface,
    EoriNumberInterface,
    IossNumberInterface
{
    /** @var string */
    protected $customerReference;
    /** @var Account */
    protected $account;
    /** @var AddressInterface */
    protected $deliveryAddress;
    /** @var AddressInterface */
    protected $collectionAddress;
    /** @var string */
    protected $deliveryInstructions;
    /** @var DateTime */
    protected $collectionDate;
    /** @var PackageInterface[] */
    protected $packages;
    /** @var bool */
    protected $signatureRequired;
    /** @var DeliveryServiceInterface */
    protected $deliveryService;
    /** @var string */
    protected $courierReference;
    /** @var bool */
    protected $isDeliveredDutyPaid;
    /** @var string */
    protected $eoriNumber;
    /** @var string */
    protected $iossNumber;

    public function __construct(
        DeliveryServiceInterface $deliveryService,
        string $customerReference,
        Account $account,
        AddressInterface $deliveryAddress,
        ?AddressInterface $collectionAddress = null,
        ?string $deliveryInstructions = null,
        ?DateTime $collectionDate = null,
        array $packages = [],
        ?bool $signatureRequired = null,
        ?bool $isDeliveredDutyPaid = null,
        ?string $eoriNumber = null,
        ?string $iossNumber = null
    ) {
        $this->deliveryService = $deliveryService;
        $this->customerReference = $customerReference;
        $this->account = $account;
        $this->deliveryAddress = $deliveryAddress;
        $this->collectionAddress = $collectionAddress;
        $this->deliveryInstructions = $deliveryInstructions;
        $this->collectionDate = $collectionDate;
        $this->packages = $packages;
        $this->signatureRequired = $signatureRequired;
        $this->isDeliveredDutyPaid = $isDeliveredDutyPaid;
        $this->eoriNumber = $eoriNumber;
        $this->iossNumber = $iossNumber;
    }

    public static function fromArray(array $array): Shipment
    {
        return new static(
            $array['deliveryService'],
            $array['customerReference'],
            $array['account'],
            $array['deliveryAddress'],
            $array['collectionAddress'] ?? null,
            $array['deliveryInstructions'] ?? null,
            $array['collectionDateTime'] ?? null,
            $array['packages'] ?? [],
            $array['signatureRequired'] ?? null,
            $array['deliveredDutyPaid'] ?? null,
            $array['eoriNumber'] ?? null,
            $array['iossNumber'] ?? null
        );
    }

    /**
     * @inheritdoc
     */
    public static function getPackageClass()
    {
        return Package::class;
    }

    /**
     * @inheritdoc
     */
    public static function createPackage(array $packageDetails)
    {
        return Package::fromArray($packageDetails);
    }

    /**
     * @inheritdoc
     */
    public function isCancellable()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isAmendable()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerReference()
    {
        return $this->customerReference;
    }

    /**
     * @inheritdoc
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @inheritdoc
     */
    public function getDeliveryAddress()
    {
        return $this->deliveryAddress;
    }

    /**
     * @inheritdoc
     */
    public function getCollectionAddress()
    {
        return $this->collectionAddress;
    }

     /**
      * @inheritdoc
      */
     public function getDeliveryInstructions()
     {
         return $this->deliveryInstructions;
     }

     /**
      * @inheritdoc
      */
     public function getCollectionDate()
     {
         return $this->collectionDate;
     }

    /**
     * @inheritdoc
     */
    public function getPackages()
    {
        return $this->packages;
    }

    /**
     * @inheritdoc
     */
    public function isSignatureRequired()
    {
        return $this->signatureRequired;
    }

     /**
      * @inheritdoc
      */
     public function getDeliveryService()
     {
         return $this->deliveryService;
     }

     /**
      * @inheritdoc
      */
     public function getCourierReference()
     {
         return $this->courierReference;
     }

    /**
     * @inheritdoc
     */
    public function getLabels()
    {
        $labels = [];
        foreach ($this->packages as $package) {
            if (!$package->getLabel()) {
                continue;
            }
            $labels[] = $package->getLabel();
        }
        return $labels;
    }

    /**
     * @inheritdoc
     */
    public function getTrackingReferences()
    {
        $references = [];
        foreach ($this->packages as $package) {
            $references[] = $package->getTrackingReference();
        }
        return $references;
    }

    public function setCourierReference(string $courierReference): Shipment
    {
        $this->courierReference = $courierReference;
        return $this;
    }

    public function isDeliveredDutyPaid(): bool
    {
        return $this->isDeliveredDutyPaid;
    }

    public function getEoriNumber(): ?string
    {
        return $this->eoriNumber;
    }

    public function setEoriNumber(?string $eoriNumber): Shipment
    {
        $this->eoriNumber = $eoriNumber;
        return $this;
    }

    public function getIossNumber(): ?string
    {
        return $this->iossNumber;
    }

    public function setIossNumber(?string $iossNumber): Shipment
    {
        $this->iossNumber = $iossNumber;
        return $this;
    }
}