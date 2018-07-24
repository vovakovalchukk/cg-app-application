<?php
namespace CG\Hermes;

use CG\CourierAdapter\Account;
use CG\CourierAdapter\AddressInterface;
use CG\CourierAdapter\DeliveryServiceInterface;
use CG\CourierAdapter\LabelInterface;
use CG\CourierAdapter\PackageInterface;
use CG\CourierAdapter\ShipmentInterface;
use CG\CourierAdapter\Shipment\SupportedField\CollectionDateInterface;
use CG\CourierAdapter\Shipment\SupportedField\DeliveryInstructionsInterface;
use CG\CourierAdapter\Shipment\SupportedField\PackagesInterface;
use CG\CourierAdapter\Shipment\SupportedField\SignatureRequiredInterface;
use CG\Hermes\Shipment\Package;
use DateTime;

class Shipment implements
    ShipmentInterface,
    DeliveryInstructionsInterface,
    CollectionDateInterface,
    PackagesInterface,
    SignatureRequiredInterface
{
    /** @var string */
    protected $customerReference;
    /** @var Account */
    protected $account;
    /** @var AddressInterface */
    protected $deliveryAddress;
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
    /** @var LabelInterface[] */
    protected $labels = [];
    /** @var string[] */
    protected $trackingReferences = [];

    public function __construct(
        string $customerReference,
        Account $account,
        AddressInterface $deliveryAddress,
        string $deliveryInstructions,
        DateTime $collectionDate,
        array $packages,
        bool $signatureRequired,
        DeliveryServiceInterface $deliveryService
    ) {
        $this->customerReference = $customerReference;
        $this->account = $account;
        $this->deliveryAddress = $deliveryAddress;
        $this->deliveryInstructions = $deliveryInstructions;
        $this->collectionDate = $collectionDate;
        $this->packages = $packages;
        $this->signatureRequired = $signatureRequired;
        $this->deliveryService = $deliveryService;
    }

    public static function fromArray(array $array): Shipment
    {
        return new static(
            $array['customerReference'],
            $array['account'],
            $array['deliveryAddress'],
            $array['deliveryInstructions'],
            $array['collectionDate'],
            $array['packages'],
            $array['signatureRequired'],
            $array['deliveryService']
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
     * Create a ShipmentInterface object from an array of details. Allowed values are:
     * [
     *     weight => float (optional)
     *     height => float (optional)
     *     width => float (optional)
     *     length => float (optional)
     *     type => Package\TypeInterface (optional)
     *     contents => Package\ContentInterface[] (optional)
     *     number => integer
     * ]
     *
     * @param array $packageDetails
     * @return PackageInterface
     */
    public static function createPackage(array $packageDetails)
    {
        // To be done in TAC-172
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
        return $this->labels;
    }

    /**
     * @inheritdoc
     */
    public function getTrackingReferences()
    {
        $this->trackingReferences;
    }

    public function setCourierReference(string $courierReference): ShipmentAbstract
    {
        $this->courierReference = $courierReference;
        return $this;
    }

    public function addLabel(LabelInterface $label): ShipmentAbstract
    {
        $this->labels[] = $label;
        return $this;
    }

    public function addTrackingReference(string $trackingReference): ShipmentAbstract
    {
        $this->trackingReferences[] = $trackingReference;
        return $this;
    }
 }