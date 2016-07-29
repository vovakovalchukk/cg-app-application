<?php
namespace CourierAdapter\Account;

use CG\Account\Client\Service as OHAccountService;
use CG\Account\Shared\Entity as AccountEntity;
use CG\CourierAdapter\Account\CredentialRequest\TestPackFile;
use CG\CourierAdapter\Account\CredentialRequest\TestPackInterface;
use CG\CourierAdapter\CourierInterface;
use CG\CourierAdapter\DeliveryServiceInterface;
use CG\CourierAdapter\Provider\Account\Mapper as CAAccountMapper;
use CG\CourierAdapter\Provider\Implementation\Service as AdapterImplementationService;
use CG\CourierAdapter\Provider\Label\Create as CALabelCreateService;
use CG\CourierAdapter\Package\SupportedField as PackageField;
use CG\CourierAdapter\Shipment\SupportedField as ShipmentField;
use CG\Order\Client\Service as OHOrderService;
use CG\Order\Service\Filter as OHOrderFilter;
use CG\Order\Shared\Entity as OHOrder;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use DateTime;
use InvalidArgumentException;
use RuntimeException;

class TestPackGenerator
{
    const EXAMPLE_ITEM_WEIGHT_KG = 0.1;

    /** @var OHAccountService */
    protected $ohAccountService;
    /** @var AdapterImplementationService */
    protected $adapterImplementationService;
    /** @var CAAccountMapper */
    protected $caAccountMapper;
    /** @var OHOrderService */
    protected $ohOrderService;
    /** @var CALabelCreateService */
    protected $caLabelCreateService;
    /** @var OrganisationUnitService */
    protected $organisationUnitService;

    public function __construct(
        OHAccountService $ohAccountService,
        AdapterImplementationService $adapterImplementationService,
        CAAccountMapper $caAccountMapper,
        OHOrderService $ohOrderService,
        CALabelCreateService $caLabelCreateService,
        OrganisationUnitService $organisationUnitService
    ) {
        $this->setOHAccountService($ohAccountService)
            ->setAdapterImplementationService($adapterImplementationService)
            ->setCAAccountMapper($caAccountMapper)
            ->setOHOrderService($ohOrderService)
            ->setCALabelCreateService($caLabelCreateService)
            ->setOrganisationUnitService($organisationUnitService);
    }

    /**
     * @return string dataUri
     */
    public function __invoke($accountId, $fileReference)
    {
        $account = $this->ohAccountService->fetch($accountId);
        $courierInstance = $this->getCourierInstanceForChannel($account->getChannel(), TestPackInterface::class);

        $testPackFileToGenerate = null;
        foreach ($courierInstance->getTestPackFileList() as $testPackFile) {
            if ($testPackFile->getReference() == $fileReference) {
                $testPackFileToGenerate = $testPackFile;
                break;
            }
        }
        if (!$testPackFileToGenerate) {
            throw new InvalidArgumentException('No test pack file with reference "' . $fileReference . '" found');
        }

        $caAccount = $this->caAccountMapper->fromOHAccount($account);
        $shipments = $this->generateTestPackFileShipmentsForAccount($account, $testPackFileToGenerate, $courierInstance);

        return $courierInstance->generateTestPackFile($testPackFileToGenerate, $caAccount, $shipments);
    }

    protected function generateTestPackFileShipmentsForAccount(
        AccountEntity $account,
        TestPackFile $testPackFile,
        CourierInterface $courierInstance
    ) {
        $ordersArray = iterator_to_array($this->fetchExampleOrdersForAccountTestPackFile($account, $testPackFile));
        $deliveryServices = $this->fetchDeliveryServicesForAccountTestPackFile($account, $testPackFile, $courierInstance);

        $shipments = [];
        $order = null;
        $deliveryService = null;
        for ($count = 0; $count < $testPackFile->getRequiredShipmentCount(); $count++) {
            $order = $this->getNextOrderForTestPackShipments($ordersArray, $order);
            $deliveryService = $this->getNextDeliveryServiceForTestPackShipments($order, $deliveryServices, $deliveryService);

            $orderData = $this->getExampleOrderData($order, $deliveryService);
            $parcelsData = $this->getExampleParcelsData($order, $deliveryService);
            $itemsData = $this->getExampleItemsData($order, $deliveryService);
            $organisationUnit = $this->getOrganisationUnitForAccount($account);

            $shipments[] = $this->caLabelCreateService->createShipmentForOrderAndData(
                $order, $orderData, $parcelsData, $itemsData, $account, $organisationUnit, $deliveryService
            );
        }

        return $shipments;
    }

    protected function fetchExampleOrdersForAccountTestPackFile(AccountEntity $account, TestPackFile $testPackFile)
    {
        $filter = (new OHOrderFilter())
            ->setLimit($testPackFile->getRequiredShipmentCount())
            ->setPage(1)
            ->setOrganisationUnitId([$account->getOrganisationUnitId()]);
        if (!empty($testPackFile->getAllowedShipmentISOAlpha2CountryCodes())) {
            $filter->setShippingAddressCountry($testPackFile->getAllowedShipmentISOAlpha2CountryCodes());
        }

        return $this->ohOrderService->fetchCollectionByFilter($filter);
    }

    protected function fetchDeliveryServicesForAccountTestPackFile(
        AccountEntity $account,
        TestPackFile $testPackFile,
        CourierInterface $courierInstance
    ) {
        if (!empty($testPackFile->getAllowedShipmentDeliveryServices())) {
            return $testPackFile->getAllowedShipmentDeliveryServices();
        }

        $caAccount = $this->caAccountMapper->fromOHAccount($account);
        return $courierInstance->fetchDeliveryServicesForAccount($caAccount);
    }

    protected function getNextOrderForTestPackShipments(array &$ordersArray, OHOrder $currentOrder = null)
    {
        if (empty($ordersArray) && $currentOrder) {
            // If we've run out of orders just use the last one
            return $currentOrder;
        }
        return array_shift($ordersArray);
    }

    protected function getNextDeliveryServiceForTestPackShipments(
        OHOrder $order,
        array &$deliveryServices,
        DeliveryServiceInterface $currentDeliveryService = null
    ) {
        if (empty($deliveryServices) && $currentDeliveryService) {
            // If we've run out of services just use the last one
            return $currentDeliveryService;
        }

        // Get the next delivery service that supports sending to the country of our order
        $countryCode = $order->getShippingAddressCountryCodeForCourier();
        for ($count = 0; $count < count($deliveryServices); $count++) {
            $potentialDeliveryService = $deliveryServices[$count];

            if (!$potentialDeliveryService->isISOAlpha2CountryCodeSupported($countryCode)) {
                continue;
            }
            $deliveryService = $potentialDeliveryService;
            // Take it out of the list so we don't use it again as we want a mix of services if possible
            array_splice($deliveryServices, $count, 1);
            return $deliveryService;
        }

        // Reached the end of the delivery services without returning one, this is unexpected.
        throw new RuntimeException(
            sprintf('No suitable delivery services found for Order %s, country %s', $order->getId(), $countryCode)
        );
    }

    protected function getExampleOrderData(OHOrder $order, DeliveryServiceInterface $deliveryService)
    {
        $orderData = [];
        $shipmentClass = $deliveryService->getShipmentClass();

        if (is_a($shipmentClass, ShipmentField\CollectionDateInterface::class, true) ||
            is_a($shipmentClass, ShipmentField\CollectionTimeInterface::class, true)
        ) {
            $orderData['collectionDateTime'] = new DateTime();
        }
        if (is_a($shipmentClass, ShipmentField\DeliveryInstructionsInterface::class, true)) {
            $orderData['deliveryInstructions'] = 'Example delivery instructions';
        }
        if (is_a($shipmentClass, ShipmentField\InsuranceRequiredInterface::class, true)) {
            $orderData['insurance'] = false;
        }
        if (is_a($shipmentClass, ShipmentField\InsuranceAmountInterface::class, true)) {
            $orderData['insuranceMonetary'] = 0;
        }
        if (is_a($shipmentClass, ShipmentField\SignatureRequiredInterface::class, true)) {
            $orderData['signature'] = false;
        }

        return $orderData;
    }

    protected function getExampleParcelsData(OHOrder $order, DeliveryServiceInterface $deliveryService)
    {
        $shipmentClass = $deliveryService->getShipmentClass();
        $parcelsData = [];
        if (!is_a($shipmentClass, ShipmentField\PackagesInterface::class, true)) {
            return $parcelsData;
        }

        $packageClass = call_user_func([$shipmentClass, 'getPackageClass']);
        $parcelData = [];
        if (is_a($packageClass, PackageField\DimensionsInterface::class, true)) {
            $parcelData['height'] = $parcelData['width'] = $parcelData['length'] = 1;
        }
        if (is_a($packageClass, PackageField\WeightInterface::class, true)) {
            $parcelData['weight'] = static::EXAMPLE_ITEM_WEIGHT_KG * count($order->getItems());
        }
        if (is_a($packageClass, PackageField\ContentsInterface::class, true)) {
            $itemQuantities = [];
            foreach ($order->getItems() as $item) {
                $itemQuantities[$item->getId()] = $item->getItemQuantity();
            }
            $parcelData['itemParcelAssignment'] = $itemQuantities;
        }
        if (is_a($shipmentClass, ShipmentField\PackageTypesInterface::class, true)) {
            $packageType = array_shift(call_user_func([$shipmentClass, 'getPackageTypes']));
            $parcelData['packageType'] = $packageType->getReference();
        }

        $parcelsData[] = $parcelData;
        return $parcelsData;
    }

    protected function getExampleItemsData(OHOrder $order, DeliveryServiceInterface $deliveryService)
    {
        $shipmentClass = $deliveryService->getShipmentClass();
        $itemsData = [];
        if (!is_a($shipmentClass, ShipmentField\PackagesInterface::class, true)) {
            return $itemsData;
        }

        foreach ($order->getItems() as $item) {
            $itemsData[$item->getId()] = [
                'weight' => static::EXAMPLE_ITEM_WEIGHT_KG
            ];
        }

        return $itemsData;
    }

    protected function getOrganisationUnitForAccount(AccountEntity $account)
    {
        return $this->organisationUnitService->fetch($account->getOrganisationUnitId());
    }

    protected function getCourierInstanceForChannel($channelName, $specificInterface = null)
    {
        if (!$this->adapterImplementationService->isProvidedChannel($channelName)) {
            throw new InvalidArgumentException(__METHOD__ . ' called with channel ' . $channelName . ' but that is not a channel provided by the Courier Adapters');
        }
        $adapterImplementation = $this->adapterImplementationService->getAdapterImplementationByChannelName($channelName);
        $courierInstance = $this->adapterImplementationService->getAdapterImplementationCourierInstance($adapterImplementation);
        if ($specificInterface && !$courierInstance instanceof $specificInterface) {
            throw new InvalidArgumentException(__METHOD__ . ' called with channel ' . $channelName . ' but its adapter does not implement ' . $specificInterface);
        }
        return $courierInstance;
    }

    protected function setOhAccountService(OHAccountService $ohAccountService)
    {
        $this->ohAccountService = $ohAccountService;
        return $this;
    }

    protected function setAdapterImplementationService(AdapterImplementationService $adapterImplementationService)
    {
        $this->adapterImplementationService = $adapterImplementationService;
        return $this;
    }

    protected function setCaAccountMapper(CAAccountMapper $caAccountMapper)
    {
        $this->caAccountMapper = $caAccountMapper;
        return $this;
    }

    protected function setOhOrderService(OHOrderService $ohOrderService)
    {
        $this->ohOrderService = $ohOrderService;
        return $this;
    }

    protected function setCaLabelCreateService(CALabelCreateService $caLabelCreateService)
    {
        $this->caLabelCreateService = $caLabelCreateService;
        return $this;
    }

    protected function setOrganisationUnitService(OrganisationUnitService $organisationUnitService)
    {
        $this->organisationUnitService = $organisationUnitService;
        return $this;
    }
}