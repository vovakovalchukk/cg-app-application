<?php
namespace CG\CourierAdapter\Provider\Implementation;

use CG\Account\Shared\Entity as AccountEntity;
use CG\Channel\Shipping\Provider\BookingOptionsInterface as CarrierBookingOptionsInterface;
use CG\CourierAdapter\CourierInterface;
use CG\CourierAdapter\Package\SupportedField as PackageField;
use CG\CourierAdapter\Provider\Account\Mapper as CAAccountMapper;
use CG\CourierAdapter\Provider\Implementation\Service as AdapterImplementationService;
use CG\CourierAdapter\Provider\Label\Cancel as LabelCancelService;
use CG\CourierAdapter\Shipment\SupportedField as ShipmentField;
use CG\CourierAdapter\Shipment\SupportedField\InsuranceOptionsInterface;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Courier\Label\OrderParcelsData\ParcelData\Collection as ParcelDataCollection;
use CG\Order\Shared\ShippableInterface as OrderEntity;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Product\Detail\Collection as ProductDetailCollection;
use CG\Product\Detail\Entity as ProductDetail;

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
            PackageField\ContentsInterface::class => 'itemParcelAssignment',
            PackageField\DimensionsInterface::class => ['height', 'width', 'length'],
            PackageField\WeightInterface::class => 'weight',
            PackageField\HarmonisedSystemCodeInterface::class => 'harmonisedSystemCode',
            PackageField\CountryOfOriginInterface::class => 'countryOfOrigin',
            PackageField\HarmonisedSystemCodeDescriptionInterface::class => 'harmonisedSystemCodeDescription',
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
            ShipmentField\EoriNumberInterface::class => 'eoriNumber',
        ]
    ];

    public function __construct(
        AdapterImplementationService $adapterImplementationService,
        CAAccountMapper $caAccountMapper,
        LabelCancelService $labelCancelService
    ) {
        $this->adapterImplementationService = $adapterImplementationService;
        $this->caAccountMapper = $caAccountMapper;
        $this->labelCancelService = $labelCancelService;
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
        string $serviceCode,
        CourierInterface $courierInstance = null
    ): array {
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

    protected function getCarrierBookingOptionsForShipmentClass(string $shipmentClass): array
    {
        $map = $this->optionInterfacesToOptionNameMap['shipment'];
        return $this->getCarrierBookingOptionsForDeliveryClass($shipmentClass, $map);
    }

    protected function getCarrierBookingOptionsForPackageClass(string $packageClass): array
    {
        $map = $this->optionInterfacesToOptionNameMap['package'];
        return $this->getCarrierBookingOptionsForDeliveryClass($packageClass, $map);
    }

    protected function getCarrierBookingOptionsForDeliveryClass(string $className, array $map): array
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
    public function addCarrierSpecificDataToListArray(
        array $data,
        AccountEntity $account,
        OrganisationUnit $rootOu,
        OrderCollection $orders,
        ProductDetailCollection $productDetails
    ) {
        $courierInstance = $this->adapterImplementationService->getAdapterImplementationCourierInstanceForAccount($account);
        foreach ($data as &$row) {
            $service = $this->mapServiceFromListArrayRow($row);
            if ($service) {
                $serviceOptions = $this->getCarrierBookingOptionsForService($account, $service, $courierInstance);
            } else {
                $serviceOptions = $this->getCarrierBookingOptionsForAccount($account);
            }

            foreach ($serviceOptions as $optionType) {
                if ($optionType == 'packageType') {
                    $optionType = 'packageTypes';
                }
                $order = null;
                $orderProductDetails = null;
                if (isset($row['parcelRow']) && $row['parcelRow']) {
                    $order = $orders->getById($row['orderId']);
                    $orderProductDetails = $this->getProductDetailsForOrder($productDetails, $order);
                }
                $options = $this->getDataForDeliveryServiceOption($account, $service, $optionType, $order, $orderProductDetails, $courierInstance);
                if (!$options) {
                    continue;
                }
                $row[$optionType] = $options;
            }
        }
        return $data;
    }

    protected function mapServiceFromListArrayRow(array $row): ?string
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

    protected function getProductDetailsForOrder(ProductDetailCollection $productDetails, OrderEntity $order): ProductDetailCollection
    {
        $itemSkus = $order->getItems()->getArrayOf('itemSku');
        $orderProductDetails = new ProductDetailCollection(ProductDetail::class, __FUNCTION__, ['orderId' => $order->getId()]);
        foreach ($productDetails as $productDetail) {
            if (!in_array($productDetail->getSku(), $itemSkus)) {
                continue;
            }
            $orderProductDetails->attach($productDetail);
        }
        return $orderProductDetails;
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
        return $this->getDataForDeliveryServiceOption($account, $service, $option, $order, $productDetails);
    }

    protected function getDataForDeliveryServiceOption(
        AccountEntity $account,
        ?string $serviceCode,
        string $option,
        OrderEntity $order = null,
        ProductDetailCollection $productDetails = null,
        CourierInterface $courierInstance = null
    ): ?array {
        $data = [];
        if (!$productDetails && isset($this->carrierBookingOptionData[$account->getId()][$serviceCode][$option])) {
            return $this->carrierBookingOptionData[$account->getId()][$serviceCode][$option];
        }
        if ($option != 'packageTypes' && $option != 'insuranceOptions') {
            return null;
        }
        if (!$serviceCode) {
            return null;
        }

        if (!$courierInstance) {
            $courierInstance = $this->adapterImplementationService->getAdapterImplementationCourierInstanceForAccount($account);
        }
        $deliveryService = $courierInstance->fetchDeliveryServiceByReference($serviceCode);
        $shipmentClass = $deliveryService->getShipmentClass();
        if ($option == 'packageTypes') {
            $data = $this->getDataForPackageTypesOption($shipmentClass, $order, $productDetails);
        } elseif ($option == 'insuranceOptions' && isset(class_implements($shipmentClass)[InsuranceOptionsInterface::class])) {
            $data = $this->getDataForInsuranceOptionsOption($shipmentClass);
        }
        if (!$productDetails) {
            $this->carrierBookingOptionData[$account->getId()][$serviceCode][$option] = $data;
        }
        return $data;
    }

    protected function getDataForPackageTypesOption(
        string $shipmentClass,
        OrderEntity $order = null,
        ProductDetailCollection $productDetails = null
    ): array {
        $parcelDataArray = null;
        if ($order && $productDetails) {
            $parcelDataCollection = ParcelDataCollection::fromOrderAndProductDetails($order, $productDetails);
            $parcelDataCollection->sortDimensions();
            $parcelDataArray = $parcelDataCollection->toArrayOfObjects();
        }
        $packageTypes = $shipmentClass::getPackageTypes($parcelDataArray);
        $data = [];
        foreach ($packageTypes as $packageType) {
            $data[$packageType->getReference()] = $packageType->getDisplayName();
        }
        return $data;
    }

    protected function getDataForInsuranceOptionsOption(string $shipmentClass): array
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
}