<?php
namespace CG\CourierAdapter\Provider\Label;

use CG\Account\Shared\Entity as Account;
use CG\CourierAdapter\Package\SupportedField\ContentsInterface as PackageContentsInterface;
use CG\CourierAdapter\Provider\Account\Mapper as CAAccountMapper;
use CG\CourierAdapter\Provider\Implementation\Address\Mapper as CAAddressMapper;
use CG\CourierAdapter\Provider\Implementation\Package\Content as CAPackageContent;
use CG\CourierAdapter\Shipment\SupportedField\CollectionAddressInterface;
use CG\CourierAdapter\Shipment\SupportedField\PackageTypesInterface;
use CG\CourierAdapter\Shipment\SupportedField\ShippersVatInterface;
use CG\Locale\Mass as LocaleMass;
use CG\Locale\Length as LocaleLength;
use CG\Order\Shared\ShippableInterface as Order;
use CG\Order\Shared\Item\Entity as Item;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Product\Detail\Entity as ProductDetail;
use PhpUnitsOfMeasure\PhysicalQuantity\Length;
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;

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

    public function ohParcelDataToCAPackageData(
        Order $order,
        array $parcelData,
        array $itemsData,
        $shipmentClass,
        $packageClass,
        OrganisationUnit $rootOu
    ) {
        $caPackageData = [
            'weight' => (isset($parcelData['weight']) && $parcelData['weight'] !== '' ? $this->normaliseWeight($parcelData['weight'], $rootOu->getLocale()) : null),
            'height' => (isset($parcelData['height']) && $parcelData['height'] !== '' ? $this->normaliseDimension($parcelData['height'], $rootOu->getLocale()) : null),
            'width' => (isset($parcelData['width']) && $parcelData['width'] !== '' ? $this->normaliseDimension($parcelData['width'], $rootOu->getLocale()) : null),
            'length' => (isset($parcelData['length']) && $parcelData['length'] !== '' ? $this->normaliseDimension($parcelData['length'], $rootOu->getLocale()) : null),
            'number' => (isset($parcelData['number']) && $parcelData['number'] !== '' ? $parcelData['number'] : null),
        ];
        if (isset($parcelData['packageType']) && $parcelData['packageType'] !== '' && is_a($shipmentClass, PackageTypesInterface::class, true)) {
            $caPackageData['type'] = $this->ohParcelDataToCAPackageType($parcelData, $shipmentClass);
        }
        if (isset($parcelData['itemParcelAssignment']) && $parcelData['itemParcelAssignment'] !== '' && is_a($packageClass, PackageContentsInterface::class, true)) {
            $caPackageData['contents'] = $this->ohOrderAndDataToPackageContents($order, $parcelData, $itemsData);
        }

        return $caPackageData;
    }

    protected function normaliseWeight(float $weight, string $locale): float
    {
        $localeUnit = LocaleMass::getForLocale($locale);
        if ($localeUnit == ProductDetail::UNIT_MASS) {
            return $weight;
        }
        return (new Mass($weight, $localeUnit))->toUnit(ProductDetail::UNIT_MASS);
    }

    protected function normaliseDimension(float $dimension, string $locale): float
    {
        $localeUnit = LocaleLength::getForLocale($locale);
        if ($localeUnit == ProductDetail::UNIT_LENGTH) {
            return $dimension;
        }
        return (new Length($dimension, $localeUnit))->toUnit(ProductDetail::UNIT_LENGTH);
    }

    protected function ohParcelDataToCAPackageType(array $parcelData, $shipmentClass)
    {
        return call_user_func([$shipmentClass, 'getPackageTypeByReference'], $parcelData['packageType']);;
    }

    public function ohOrderAndDataToCAShipmentData(
        Order $order,
        array $orderData,
        Account $account,
        OrganisationUnit $rootOu,
        $shipmentClass,
        array $packages = null
    ) {
        $caShipmentData = $this->ohOrderAndAccountToMinimalCAShipmentData($order, $account);
        if ($packages) {
            $caShipmentData['packages'] = $packages;
        }
        if (is_a($shipmentClass, CollectionAddressInterface::class, true)) {
            $caShipmentData['collectionAddress'] = $this->caAddressMapper->organisationUnitToCollectionAddress($rootOu);
        }
        if (isset($orderData['collectionDate'])) {
            $caShipmentData['collectionDateTime'] = $this->ohOrderDataToCollectionDateTime($orderData);
        }
        if (isset($orderData['deliveryInstructions'])) {
            $caShipmentData['deliveryInstructions'] = $orderData['deliveryInstructions'];
        }
        if (isset($orderData['insurance'])) {
            $caShipmentData['insuranceRequired'] = (bool)$orderData['insurance'];
        }
        if (isset($orderData['insuranceMonetary'])) {
            $caShipmentData['insuranceAmount'] = $orderData['insuranceMonetary'];
        }
        if (isset($orderData['signature'])) {
            $caShipmentData['signatureRequired'] = (bool)$orderData['signature'];
        }
        if (isset($orderData['saturday'])) {
            $caShipmentData['saturdayDelivery'] = (bool)$orderData['saturday'];
        }
        if (isset($orderData['insuranceOption'])) {
            $caShipmentData['insuranceOption'] = $orderData['insuranceOption'];
        }
        if (is_a($shipmentClass, ShippersVatInterface::class, true)) {
            $caShipmentData['shippersVatNumber'] = $order->getVatNumber() ?? '';
        }

        return $caShipmentData;
    }

    // Called internally and externally (by Label\Cancel)
    public function ohOrderAndAccountToMinimalCAShipmentData(
        Order $order,
        Account $account
    ) {
        return [
            'customerReference' => $order->getExternalId(),
            'account' => $this->caAccountMapper->fromOHAccount($account),
            'deliveryAddress' => $this->caAddressMapper->ohOrderToDeliveryAddress($order),
        ];
    }

    protected function ohOrderDataToCollectionDateTime(array $orderData)
    {
        $dateTimeString = $orderData['collectionDate'];
        if (isset($orderData['collectionTime'])) {
            $dateTimeString .= ' ' . $orderData['collectionTime'];
        }
        return new \DateTime($dateTimeString);
    }

    protected function ohOrderAndDataToPackageContents(
        Order $order,
        array $parcelData,
        array $itemsData
    ) {
        $contents = [];
        $items = $order->getItems();
        foreach ($parcelData['itemParcelAssignment'] as $parcelItemId => $parcelItemQty) {
            $item = $items->getById($parcelItemId);
            $itemData = $itemsData[$parcelItemId];
            $contents[] = $this->ohItemAndDataToPackageContents($item, $order, $itemData, $parcelItemQty, $parcelData);
        }
        return $contents;
    }

    protected function ohItemAndDataToPackageContents(
        Item $item,
        Order $order,
        array $itemData,
        $parcelItemQty,
        $parcelData
    ) {
        $itemUnitWeight = $itemData['weight'] / $item->getItemQuantity();
        return new CAPackageContent(
            $item->getItemName(),
            $parcelData['harmonisedSystemCode'] ?? '',
            'UK',
            $parcelItemQty,
            $itemUnitWeight,
            $item->getIndividualItemPrice(),
            $order->getCurrencyCode(),
            $item->getItemName(),
            '',
            $item->getItemSku()
        );
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
