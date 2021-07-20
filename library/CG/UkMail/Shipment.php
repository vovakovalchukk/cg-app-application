<?php
namespace CG\UkMail;

use CG\CourierAdapter\Account;
use CG\CourierAdapter\AddressInterface;
use CG\CourierAdapter\DeliveryServiceInterface;
use CG\CourierAdapter\LabelInterface;
use CG\CourierAdapter\PackageInterface;
use CG\CourierAdapter\Shipment\SupportedField\CollectionAddressInterface;
use CG\CourierAdapter\Shipment\SupportedField\CollectionDateInterface;
use CG\CourierAdapter\Shipment\SupportedField\DeliveryInstructionsInterface;
use CG\CourierAdapter\Shipment\SupportedField\PackagesInterface;
use CG\CourierAdapter\ShipmentInterface;
use CG\UkMail\Shipment\Package;
use \DateTime;

class Shipment implements
    ShipmentInterface,
    CollectionAddressInterface,
    DeliveryInstructionsInterface,
    CollectionDateInterface,
    PackagesInterface
{
    /** @var DeliveryServiceInterface */
    protected $deliveryService;
    /** @var string */
    protected $customerReference;
    /** @var Account */
    protected $account;
    /** @var AddressInterface */
    protected $deliveryAddress;
    /** @var string */
    protected $courierReference;
    /** @var AddressInterface */
    protected $collectionAddress;
    /** @var string */
    protected $deliveryInstructions;
    /** @var DateTime */
    protected $collectionDate;
    /** @var PackageInterface[] */
    protected $packages;

    public function __construct(
        DeliveryServiceInterface $deliveryService,
        string $customerReference,
        Account $account,
        AddressInterface $deliveryAddress,
        ?AddressInterface $collectionAddress = null,
        ?string $deliveryInstructions = null,
        ?DateTime $collectionDate = null,
        array $packages = []
    ) {
        $this->deliveryService = $deliveryService;
        $this->customerReference = $customerReference;
        $this->account = $account;
        $this->deliveryAddress = $deliveryAddress;
        $this->collectionAddress = $collectionAddress;
        $this->deliveryInstructions = $deliveryInstructions;
        $this->collectionDate = $collectionDate;
        $this->packages = $packages;
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
            $array['packages'] ?? []
        );
    }

    public function isCancellable()
    {
        return true;
    }

    public function isAmendable()
    {
        return false;
    }

    public function getCustomerReference()
    {
        return $this->customerReference;
    }

    public function getCourierReference()
    {
        return $this->courierReference;
    }

    public function setCourierReference(string $courierReference): Shipment
    {
        $this->courierReference = $courierReference;
        return $this;
    }

    public function getAccount()
    {
        return $this->account;
    }

    public function getDeliveryAddress()
    {
        return $this->deliveryAddress;
    }

    /**
     * @return DeliveryServiceInterface
     */
    public function getDeliveryService()
    {
        return $this->deliveryService;
    }

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

    public function getTrackingReferences()
    {
        $references = [];
        foreach ($this->packages as $package) {
            $references[] = $package->getTrackingReference();
        }
        return $references;
    }

    public function getCollectionAddress()
    {
        return $this->collectionAddress;
    }

    public function getCollectionDate()
    {
        return $this->collectionDate;
    }

    public function getDeliveryInstructions()
    {
        return $this->deliveryInstructions;
    }

    public function getPackages()
    {
        return $this->packages;
    }

    public static function getPackageClass()
    {
        return Package::class;
    }

    public static function createPackage(array $packageDetails)
    {
        return Package::fromArray($packageDetails);
    }
}