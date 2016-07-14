<?php
namespace CG\CourierAdapter\Provider\Implementation;

use CG\Account\Shared\Entity as AccountEntity;
use CG\Channel\CarrierBookingOptionsInterface;
use CG\CourierAdapter\Account as CAAccount;
use CG\CourierAdapter\CourierInterface;
use CG\CourierAdapter\Provider\Account\Mapper as CAAccountMapper;
use CG\CourierAdapter\Provider\Implementation\Service as AdapterImplementationService;
use CG\CourierAdapter\PackageInterface;
use CG\CourierAdapter\Package\SupportedField as PackageField;
use CG\CourierAdapter\ShipmentInterface;
use CG\CourierAdapter\Shipment\SupportedField as ShipmentField;
use CG\Order\Shared\Entity as OrderEntity;
use CG\OrganisationUnit\Entity as OrganisationUnit;

class CarrierBookingOptions implements CarrierBookingOptionsInterface
{
    /** @var AdapterImplementationService */
    protected $adapterImplementationService;
    /** @var CAAccountMapper */
    protected $caAccountMapper;

    protected $carrierBookingOptionsForAccount = [];
    protected $carrierBookingOptionsForService = [];

    protected $optionInterfacesToOptionNameMap = [
        'package' => [
            PackageField\ContentsInterface::class => 'itemParcelAssignment',
            PackageField\DimensionsInterface::class => ['height', 'width', 'length'],
            PackageField\TypeInterface::class => 'packageType',
            PackageField\WeightInterface::class => 'weight',
        ],
        'shipment' => [
            ShipmentField\CollectionDateInterface::class => 'collectionDate',
            ShipmentField\CollectionTimeInterface::class => 'collectionTime',
            ShipmentField\DeliveryInstructionsInterface::class => 'deliveryInstructions',
            ShipmentField\InsuranceAmountInterface::class => 'insuranceMonetary',
            ShipmentField\InsuranceOptionsInterface::class => 'insuranceOptions',
            ShipmentField\InsuranceRequiredInterface::class => 'insurance',
            ShipmentField\PackagesInterface::class => 'parcels',
            ShipmentField\SaturdayDeliveryInterface::class => 'saturdayDelivery',
            ShipmentField\SignatureRequiredInterface::class => 'signature',
        ]
    ];

    public function __construct(
        AdapterImplementationService $adapterImplementationService,
        CAAccountMapper $caAccountMapper
    ) {
        $this->setAdapterImplementationService($adapterImplementationService)
            ->setCAAccountMapper($caAccountMapper);
    }

    /**
     * @return array
     */
    public function getCarrierBookingOptionsForAccount(AccountEntity $account, $serviceCode = null)
    {
        if ($serviceCode) {
            return $this->getCarrierBookingOptionsForService($account, $serviceCode);
        }
        if (isset($this->carrierBookingOptionsForAccount[$account->getId()])) {
            return $this->carrierBookingOptionsForAccount[$account->getId()];
        }
        
        $options = [];
        $courierInstance = $this->adapterImplementationService->getAdapterImplementationCourierInstanceForAccount($account);
        $caAccount = $this->caAccountMapper->fromOHAccount($account);
        $deliveryServices = $courierInstance->fetchDeliveryServicesForAccount($caAccount);
        foreach ($deliveryServices as $deliveryService) {
            $serviceOptions = $this->getCarrierBookingOptionsForService(
                $account, $deliveryService->getReference(), $courierInstance
            );
            $options = array_merge($options, $serviceOptions);
        }

        $this->carrierBookingOptionsForAccount[$account->getId()] = $options;
        return $options;
    }

    protected function getCarrierBookingOptionsForService(
        AccountEntity $account,
        $serviceCode,
        CourierInterface $courierInstance = null
    ) {
        if (isset($this->carrierBookingOptionsForService[$account->getId()][$serviceCode])) {
            return $this->carrierBookingOptionsForService[$account->getId()][$serviceCode];
        }

        if (!$courierInstance) {
            $courierInstance = $this->adapterImplementationService->getAdapterImplementationCourierInstanceForAccount($account);
        }

        $deliveryService = $courierInstance->fetchDeliveryServiceByReference($serviceCode);
        $shipmentClass = $deliveryService->getShipmentClass();
        $options = $this->getCarrierBookingOptionsForDeliveryClass($shipmentClass);

        if (!is_a($shipmentClass, ShipmentField\PackagesInterface::class, true)) {
            $this->carrierBookingOptionsForService[$account->getId()][$serviceCode] = $options;
            return $options;
        }
        $packageClass = call_user_func([$shipmentClass, 'getPackageClass']);
        $options = array_merge($options, $this->getCarrierBookingOptionsForDeliveryClass($packageClass));

        $this->carrierBookingOptionsForService[$account->getId()][$serviceCode] = $options;
        return $options;
    }

    protected function getCarrierBookingOptionsForDeliveryClass($className)
    {
        $map = [];
        if (is_a($className, ShipmentInterface::class, true)) {
            $map = $this->optionInterfacesToOptionNameMap['shipment'];
        } elseif (is_a($className, PackageInterface::class, true)) {
            $map = $this->optionInterfacesToOptionNameMap['package'];
        }

        $options = [];
        foreach ($map as $interface => $optionNames) {
            if (!is_a($className, $interface, true)) {
                continue;
            }
            if (!is_array($optionNames)) {
                $options[$optionNames] = $optionNames;
                continue;
            }
            foreach ($optionNames as $optionName) {
                $options[$optionName] = $optionName;
            }
        }
        return $options;
    }

    /**
     * @return array 
     */
    public function addCarrierSpecificDataToListArray(array $data, AccountEntity $account)
    {
        return $data;
    }

    /**
     * @return mixed
     */
    public function getDataForCarrierBookingOption(
        $option,
        OrderEntity $order,
        AccountEntity $account,
        $service,
        OrganisationUnit $rootOu
    ) {
        return null;
    }

    /**
     * @return bool
     */
    public function isCancellationAllowedForOrder(AccountEntity $account, OrderEntity $order)
    {
        // TODO in CGIV-7246
        return false;
    }

    /**
     * @return bool
     */
    public function isProvidedAccount(AccountEntity $account)
    {
        return $this->adapterImplementationService->isProvidedAccount($account);
    }

    /**
     * @return bool
     */
    public function isProvidedChannel($channelName)
    {
        return $this->adapterImplementationService->isProvidedChannel($channelName);
    }

    protected function setAdapterImplementationService(AdapterImplementationService $adapterImplementationService)
    {
        $this->adapterImplementationService = $adapterImplementationService;
        return $this;
    }

    protected function setCAAccountMapper(CAAccountMapper $caAccountMapper)
    {
        $this->caAccountMapper = $caAccountMapper;
        return $this;
    }
}