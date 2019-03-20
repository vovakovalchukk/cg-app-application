<?php
namespace CG\RoyalMailApi;

use CG\CourierAdapter\Account;
use CG\CourierAdapter\AddressInterface;
use CG\CourierAdapter\DeliveryServiceInterface;
use CG\CourierAdapter\PackageInterface;
use CG\CourierAdapter\Shipment\SupportedField\PackageTypesInterface;
use CG\CourierAdapter\ShipmentInterface;
use CG\CourierAdapter\Shipment\SupportedField\CollectionDateInterface;
use CG\CourierAdapter\Shipment\SupportedField\DeliveryInstructionsInterface;
use CG\CourierAdapter\Shipment\SupportedField\PackagesInterface;
use CG\CourierAdapter\Shipment\SupportedField\SignatureRequiredInterface;
use CG\RoyalMailApi\Shipment\Package;
use CG\RoyalMailApi\Package\Type;
use DateTime;

class Shipment implements
    ShipmentInterface,
    DeliveryInstructionsInterface,
    CollectionDateInterface,
    PackagesInterface,
    PackageTypesInterface,
    SignatureRequiredInterface
{
    const packageTypes = [
        'L' => 'Letter',
        'F' => 'Large Letter',
        'P' => 'Parcel',
    ];

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

    public function __construct(
        DeliveryServiceInterface $deliveryService,
        string $customerReference,
        Account $account,
        AddressInterface $deliveryAddress,
        ?AddressInterface $collectionAddress = null,
        ?string $deliveryInstructions = null,
        ?DateTime $collectionDate = null,
        array $packages = [],
        ?bool $signatureRequired = null
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
    }

    public static function fromArray(array $array): Shipment
    {
        return new static(
            $array['deliveryService'],
            $array['customerReference'],
            $array['account'],
            $array['deliveryAddress'],
            $array['deliveryInstructions'] ?? null,
            $array['collectionDateTime'] ?? null,
            $array['packages'] ?? [],
            $array['signatureRequired'] ?? null
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

    /**
     * @inheritdoc
     */
    public static function getPackageTypes()
    {
        $packageTypes = [];
        foreach (static::packageTypes as $packageReference => $packageDisplayName) {
            $packageTypes[] = Type::fromArray(
                [
                    'reference' => $packageReference,
                    'displayName' => $packageDisplayName
                ]
            );
        }
        return $packageTypes;
    }

    /**
     * @inheritdoc
     */
    public static function getPackageTypeByReference($reference)
    {
        $packageTypes = [];
        foreach (static::packageTypes as $packageReference => $packageDisplayName) {
            $packageTypes[] = Type::fromArray(
                [
                    'reference' => $packageReference,
                    'displayName' => $packageDisplayName
                ]
            );
        }
        return $packageTypes[$reference];
    }
}