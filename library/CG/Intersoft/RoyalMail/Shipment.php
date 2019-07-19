<?php
namespace CG\Intersoft\RoyalMail;

use CG\CourierAdapter\Account;
use CG\CourierAdapter\AddressInterface;
use CG\CourierAdapter\DeliveryServiceInterface;
use CG\CourierAdapter\Package\SupportedField\WeightAndDimensionsInterface;
use CG\CourierAdapter\PackageInterface;
use CG\CourierAdapter\Shipment\SupportedField\CollectionDateInterface;
use CG\CourierAdapter\Shipment\SupportedField\DeliveryInstructionsInterface;
use CG\CourierAdapter\Shipment\SupportedField\PackagesInterface;
use CG\CourierAdapter\Shipment\SupportedField\PackageTypesInterface;
use CG\CourierAdapter\Shipment\SupportedField\SignatureRequiredInterface;
use CG\CourierAdapter\ShipmentInterface;
use CG\CourierAdapter\Shipment\SupportedField\CollectionAddressInterface;
use CG\Intersoft\RoyalMail\PackageType\Decider\Factory as PackageTypeDeciderFactory;
use CG\Intersoft\RoyalMail\Shipment\Package\Type as PackageType;
use CG\Intersoft\RoyalMail\Shipment\Package;
use CG\Stdlib\Exception\Runtime\NotFound;
use DateTime;
use CG\CourierAdapter\InsuranceOptionInterface as InsuranceOption;

class Shipment implements
    ShipmentInterface,
    DeliveryInstructionsInterface,
    CollectionAddressInterface,
    CollectionDateInterface,
    PackagesInterface,
    PackageTypesInterface,
    SignatureRequiredInterface
{
    protected static $packageTypes = [
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
    /** @var InsuranceOption */
    protected $insuranceOption;
    /** @var AddressInterface */
    protected $collectionAddress;

    public function __construct(
        DeliveryServiceInterface $deliveryService,
        string $customerReference,
        Account $account,
        AddressInterface $deliveryAddress,
        ?InsuranceOption $insuranceOption,
        ?AddressInterface $collectionAddress = null,
        array $packages = [],
        ?string $deliveryInstructions = null,
        ?DateTime $collectionDate = null,
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
        $this->insuranceOption = $insuranceOption;
    }

    public static function fromArray(array $array): Shipment
    {
        return new static(
            $array['deliveryService'],
            $array['customerReference'],
            $array['account'],
            $array['deliveryAddress'],
            $array['insuranceOption'] ?? null,
            $array['collectionAddress'] ?? null,
            $array['packages'] ?? [],
            $array['deliveryInstructions'] ?? null,
            $array['collectionDateTime'] ?? null,
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
    public static function getPackageTypes(WeightAndDimensionsInterface $weightAndDimensions = null)
    {
        $packageTypes = static::getAllPackageTypes();
        if (!$weightAndDimensions) {
            return $packageTypes;
        }
        $packageTypeDecider = PackageTypeDeciderFactory::getForShipmentClass(static::class);
        return $packageTypeDecider($packageTypes, $weightAndDimensions);
    }

    protected static function getAllPackageTypes(): array
    {
        $packageTypes = [];
        foreach (static::$packageTypes as $packageReference => $packageDisplayName) {
            $packageTypes[] = PackageType::fromArray(
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
        if (!isset(static::$packageTypes[$reference])) {
            throw new NotFound('No package type available for reference ' . $reference);
        }

        return PackageType::fromArray(
            [
                'reference' => $reference,
                'displayName' => static::$packageTypes[$reference]
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getInsuranceOption()
    {
        return $this->insuranceOption;
    }

    /**
     * @return AddressInterface
     */
    public function getCollectionAddress()
    {
        return $this->collectionAddress;
    }

    public static function isDomestic(): bool
    {
        return true;
    }

    public static function isInternational(): bool
    {
        return !static::isDomestic();
    }
}