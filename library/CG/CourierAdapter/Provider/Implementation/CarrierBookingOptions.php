<?php
namespace CG\CourierAdapter\Provider\Implementation;

use CG\Account\Shared\Entity as AccountEntity;
use CG\Channel\Shipping\Provider\BookingOptionsInterface as CarrierBookingOptionsInterface;
use CG\CourierAdapter\Account as CAAccount;
use CG\CourierAdapter\CourierInterface;
use CG\CourierAdapter\Provider\Account\Mapper as CAAccountMapper;
use CG\CourierAdapter\Provider\Implementation\Service as AdapterImplementationService;
use CG\CourierAdapter\Provider\Label\Cancel as LabelCancelService;
use CG\CourierAdapter\PackageInterface;
use CG\CourierAdapter\Package\SupportedField as PackageField;
use CG\CourierAdapter\ShipmentInterface;
use CG\CourierAdapter\Shipment\SupportedField as ShipmentField;
use CG\Order\Shared\ShippableInterface as OrderEntity;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Product\Detail\Collection as ProductDetailCollection;
use CG\CourierAdapter\Shipment\SupportedField\InsuranceOptionsInterface;

class CarrierBookingOptions implements CarrierBookingOptionsInterface
{
    /** @var AdapterImplementationService */
    protected $adapterImplementationService;
    /** @var CAAccountMapper */
    protected $caAccountMapper;
    /** @var LabelCancelService */
    protected $labelCancelService;

    protected $carrierBookingOptionsForAccount = [];
    protected $carrierBookingOptionsForService = [];
    protected $orderServiceMap = [];
    protected $carrierBookingOptionData = [];

    protected $optionInterfacesToOptionNameMap = [
        'package' => [
            PackageField\ContentsInterface::class => ['itemParcelAssignment'],
            PackageField\DimensionsInterface::class => ['height', 'width', 'length'],
            PackageField\WeightInterface::class => 'weight',
            PackageField\HarmonisedSystemCodeInterface::class => 'harmonisedSystemCode',
        ],
        'shipment' => [
            ShipmentField\CollectionDateInterface::class => 'collectionDate',
            ShipmentField\CollectionTimeInterface::class => 'collectionTime',
            ShipmentField\DeliveryInstructionsInterface::class => 'deliveryInstructions',
            ShipmentField\InsuranceAmountInterface::class => 'insuranceMonetary',
            ShipmentField\InsuranceOptionsInterface::class => 'insuranceOptions',
            ShipmentField\InsuranceRequiredInterface::class => 'insurance',
            ShipmentField\PackagesInterface::class => 'parcels',
            ShipmentField\PackageTypesInterface::class => 'packageType',
            ShipmentField\SaturdayDeliveryInterface::class => 'saturday',
            ShipmentField\SignatureRequiredInterface::class => 'signature',
        ]
    ];

    public function __construct(
        AdapterImplementationService $adapterImplementationService,
        CAAccountMapper $caAccountMapper,
        LabelCancelService $labelCancelService
    ) {
        $this->setAdapterImplementationService($adapterImplementationService)
            ->setCAAccountMapper($caAccountMapper)
            ->setLabelCancelService($labelCancelService);
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
        $options = $this->getCarrierBookingOptionsForShipmentClass($shipmentClass);

        if (!is_a($shipmentClass, ShipmentField\PackagesInterface::class, true)) {
            $this->carrierBookingOptionsForService[$account->getId()][$serviceCode] = $options;
            return $options;
        }
        $packageClass = call_user_func([$shipmentClass, 'getPackageClass']);
        $options = array_merge($options, $this->getCarrierBookingOptionsForPackageClass($packageClass));

        $this->carrierBookingOptionsForService[$account->getId()][$serviceCode] = $options;
        return $options;
    }

    protected function getCarrierBookingOptionsForShipmentClass($shipmentClass)
    {
        $map = $this->optionInterfacesToOptionNameMap['shipment'];
        return $this->getCarrierBookingOptionsForDeliveryClass($shipmentClass, $map);
    }

    protected function getCarrierBookingOptionsForPackageClass($packageClass)
    {
        $map = $this->optionInterfacesToOptionNameMap['package'];
        return $this->getCarrierBookingOptionsForDeliveryClass($packageClass, $map);
    }

    protected function getCarrierBookingOptionsForDeliveryClass($className, array $map)
    {
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
        $courierInstance = $this->adapterImplementationService->getAdapterImplementationCourierInstanceForAccount($account);
        foreach ($data as &$row) {
            $service = $this->mapServiceFromListArrayRow($row);
            if ($service) {
                $serviceOptions = $this->getCarrierBookingOptionsForService($account, $service, $courierInstance);
            } else {
                $serviceOptions = $this->getCarrierBookingOptionsForAccount($account);
            }

            foreach ($serviceOptions as $optionType) {
                $optionReference = $optionType;
                if ($optionReference == 'packageType') {
                    $optionReference = 'packageTypes';
                }
                $options = $this->getDataForDeliveryServiceOption($account, $service, $optionReference, $courierInstance);
                if (!$options) {
                    continue;
                }
                $row[$optionType] = $options;
            }
        }
        return $data;
    }

    protected function mapServiceFromListArrayRow(array $row)
    {
        if ($row['orderRow']) {
            $this->orderServiceMap[$row['orderId']] = $row['service'];
            return $row['service'];
        }
        if (isset($this->orderServiceMap[$row['orderId']])) {
            return $this->orderServiceMap[$row['orderId']];
        }
        return null;
    }

    /**
     * @return mixed
     */
    public function getDataForCarrierBookingOption(
        $option,
        OrderEntity $order,
        AccountEntity $account,
        $service,
        OrganisationUnit $rootOu,
        ProductDetailCollection $productDetails
    ) {
        return $this->getDataForDeliveryServiceOption($account, $service, $option);
    }

    protected function getDataForDeliveryServiceOption(
        AccountEntity $account,
        $serviceCode,
        $option,
        CourierInterface $courierInstance = null
    ) {
        $data = [];
        if (isset($this->carrierBookingOptionData[$account->getId()][$option])) {
            return $this->carrierBookingOptionData[$account->getId()][$option];
        }
        if ($option != 'packageTypes' && $option != 'insuranceOptions') {
            return null;
        }

        if (!$courierInstance) {
            $courierInstance = $this->adapterImplementationService->getAdapterImplementationCourierInstanceForAccount($account);
        }
        $deliveryService = $courierInstance->fetchDeliveryServiceByReference($serviceCode);
        $shipmentClass = $deliveryService->getShipmentClass();
        if ($option == 'packageTypes') {
            $data = $this->getDataForPackageTypesOption($shipmentClass);
        } elseif ($option == 'insuranceOptions' && isset(class_implements($shipmentClass)[InsuranceOptionsInterface::class])) {
            $data = $this->getDataForInsuranceOptionsOption($shipmentClass);
        }

        $this->carrierBookingOptionData[$account->getId()][$option] = $data;
        return $data;
    }

    protected function getDataForPackageTypesOption($shipmentClass)
    {
        $packageTypes = call_user_func([$shipmentClass, 'getPackageTypes']);
        $data = [];
        foreach ($packageTypes as $packageType) {
            $data[$packageType->getReference()] = $packageType->getDisplayName();
        }
        return $data;
    }

    protected function getDataForInsuranceOptionsOption($shipmentClass)
    {
        $insuranceOptions = call_user_func([$shipmentClass, 'getAvailableInsuranceOptions']);
        $data = [];
        foreach ($insuranceOptions as $insuranceOption) {
            $data[$insuranceOption->getReference()] = $insuranceOption->getDisplayName();
        }
        return $data;
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

    protected function setLabelCancelService(LabelCancelService $labelCancelService)
    {
        $this->labelCancelService = $labelCancelService;
        return $this;
    }
}