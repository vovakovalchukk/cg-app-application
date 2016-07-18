<?php
namespace CG\CourierAdapter\Provider\Label;

use CG\Account\Shared\Entity as OHAccount;
use CG\CourierAdapter\Provider\Account\Mapper as CAAccountMapper;
use CG\CourierAdapter\Provider\Implementation\Address as CAAddress;
use CG\CourierAdapter\Shipment\SupportedField\CollectionAddressInterface;
use CG\CourierAdapter\Shipment\SupportedField\PackageTypesInterface;
use CG\Order\Shared\Entity as OHOrder;
use CG\OrganisationUnit\Entity as OrganisationUnit;

class Mapper
{
    /** @var CAAccountMapper */
    protected $caAccountMapper;

    public function __construct(CAAccountMapper $caAccountMapper)
    {
        $this->setCaAccountMapper($caAccountMapper);
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
        $caShipmentData = [
            'customerReference' => $ohOrder->getId(),
            'account' => $this->caAccountMapper->fromOHAccount($ohAccount),
            'deliveryAddress' => $this->ohOrderToDeliveryAddress($ohOrder),
        ];
        if ($packages) {
            $caShipmentData['packages'] = $packages;
        }
        if (is_a($shipmentClass, CollectionAddressInterface::class, true)) {
            $caShipmentData['collectionAddress'] = $this->organisationUnitToCollectionAddress($rootOu);
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

    protected function ohOrderToDeliveryAddress(OHOrder $ohOrder)
    {
        list($firstName, $lastName) = $this->getFirstAndLastNameFromFullName($ohOrder->getShippingAddressFullNameForCourier());

        return new CAAddress(
            $firstName,
            $lastName,
            $ohOrder->getShippingAddress1ForCourier(),
            $ohOrder->getShippingAddress2ForCourier(),
            $ohOrder->getShippingAddressCityForCourier(),
            $ohOrder->getShippingAddressCountyForCourier(),
            $ohOrder->getShippingAddressPostcodeForCourier(),
            $ohOrder->getShippingAddressCountryForCourier(),
            $ohOrder->getShippingAddressCountryCodeForCourier(),
            $ohOrder->getShippingEmailAddressForCourier(),
            $ohOrder->getShippingPhoneNumberForCourier()
        );
    }

    protected function organisationUnitToCollectionAddress(OrganisationUnit $ou)
    {
        list($firstName, $lastName) = $this->getFirstAndLastNameFromFullName($ou->getAddressFullName());

        return new CAAddress(
            $firstName,
            $lastName,
            $ou->getAddress1(),
            $ou->getAddress2(),
            $ou->getAddressCity(),
            $ou->getAddressCounty(),
            $ou->getAddressPostcode(),
            $ou->getAddressCountry(),
            $ou->getAddressCountryCode(),
            $ou->getEmailAddress(),
            $ou->getPhoneNumber()
        );
    }

    protected function getFirstAndLastNameFromFullName($fullName)
    {
        $nameParts = explode(' ', $fullName);
        $firstName = array_shift($nameParts);
        $lastName = (!empty($nameParts) ? implode(' ', $nameParts) : $firstName);

        return [$firstName, $lastName];
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
}
