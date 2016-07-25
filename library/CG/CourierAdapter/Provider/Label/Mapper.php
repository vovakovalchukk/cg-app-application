<?php
namespace CG\CourierAdapter\Provider\Label;

use CG\Account\Shared\Entity as OHAccount;
use CG\CourierAdapter\Provider\Account\Mapper as CAAccountMapper;
use CG\CourierAdapter\Provider\Implementation\Address\Mapper as CAAddressMapper;
use CG\CourierAdapter\Shipment\SupportedField\CollectionAddressInterface;
use CG\CourierAdapter\Shipment\SupportedField\PackageTypesInterface;
use CG\Order\Shared\Entity as OHOrder;
use CG\OrganisationUnit\Entity as OrganisationUnit;

class Mapper
{
    /** @var CAAccountMapper */
    protected $caAccountMapper;
    /** @var CAAddressMapper */
    protected $caAddressMapper;

    public function __construct(CAAccountMapper $caAccountMapper, CAAddressMapper $caAddressMapper)
    {
        $this->setCaAccountMapper($caAccountMapper)
            ->setCAAddressMapper($caAddressMapper);
    }

    public function ohParcelDataToCAPackageData(array $ohParcelData, $shipmentClass, $packageClass)
    {
        $caPackageData = [
            'weight' => (isset($ohParcelData['weight']) ? $ohParcelData['weight'] : null),
            'height' => (isset($ohParcelData['height']) ? $ohParcelData['height'] : null),
            'width' => (isset($ohParcelData['width']) ? $ohParcelData['width'] : null),
            'length' => (isset($ohParcelData['length']) ? $ohParcelData['length'] : null),
        ];
        if (isset($ohParcelData['packageType']) && is_a($shipmentClass, PackageTypesInterface::class, true)) {
            $caPackageData['type'] = $this->ohParcelDataToCAPackageType($ohParcelData, $shipmentClass);
        }
        // Deliberately NOT checking for $ohParcelData['itemParcelAssignment'] as the CA code is only partially implemented
        // for package contents (we have no way to get the concrete implementation of Package\ContentInterface) and we
        // don't have any use cases for it at the moment.

        return $caPackageData;
    }

    protected function ohParcelDataToCAPackageType(array $ohParcelData, $shipmentClass)
    {
        return call_user_func([$shipmentClass, 'getPackageTypeByReference'], $ohParcelData['packageType']);;
    }

    public function ohOrderAndDataToCAShipmentData(
        OHOrder $ohOrder,
        array $ohOrderData,
        OHAccount $ohAccount,
        OrganisationUnit $rootOu,
        $shipmentClass,
        array $packages = null
    ) {
        $caShipmentData = $this->ohOrderAndAccountToMinimalCAShipmentData($ohOrder, $ohAccount);
        if ($packages) {
            $caShipmentData['packages'] = $packages;
        }
        if (is_a($shipmentClass, CollectionAddressInterface::class, true)) {
            $caShipmentData['collectionAddress'] = $this->caAddressMapper->organisationUnitToCollectionAddress($rootOu);
        }
        if (isset($ohOrderData['collectionDate'])) {
            $caShipmentData['collectionDateTime'] = $this->ohOrderDataToCollectionDateTime($ohOrderData);
        }
        if (isset($ohOrderData['deliveryInstructions'])) {
            $caShipmentData['deliveryInstructions'] = $ohOrderData['deliveryInstructions'];
        }
        if (isset($ohOrderData['insurance'])) {
            $caShipmentData['insuranceRequired'] = (bool)$ohOrderData['insurance'];
        }
        if (isset($ohOrderData['insuranceMonetary'])) {
            $caShipmentData['insuranceAmount'] = $ohOrderData['insuranceMonetary'];
        }
        if (isset($ohOrderData['signature'])) {
            $caShipmentData['signatureRequired'] = (bool)$ohOrderData['signature'];
        }

        return $caShipmentData;
    }

    // Called internally and externally (by Label\Cancel)
    public function ohOrderAndAccountToMinimalCAShipmentData(
        OHOrder $ohOrder,
        OHAccount $ohAccount
    ) {
        return [
            'customerReference' => $ohOrder->getId(),
            'account' => $this->caAccountMapper->fromOHAccount($ohAccount),
            'deliveryAddress' => $this->caAddressMapper->ohOrderToDeliveryAddress($ohOrder),
        ];
    }

    protected function ohOrderDataToCollectionDateTime(array $ohOrderData)
    {
        $dateTimeString = $ohOrderData['collectionDate'];
        if (isset($ohOrderData['collectionTime'])) {
            $dateTimeString .= ' ' . $ohOrderData['collectionTime'];
        }
        return new \DateTime($dateTimeString);
    }

    protected function setCaAccountMapper(CAAccountMapper $caAccountMapper)
    {
        $this->caAccountMapper = $caAccountMapper;
        return $this;
    }

    protected function setCAAddressMapper(CAAddressMapper $caAddressMapper)
    {
        $this->caAddressMapper = $caAddressMapper;
        return $this;
    }
}
