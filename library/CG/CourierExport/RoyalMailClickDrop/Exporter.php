<?php
namespace CG\CourierExport\RoyalMailClickDrop;

use CG\Channel\Shipping\Provider\Service\ExportDocumentInterface;
use CG\CourierExport\ExporterInterface;
use CG\Order\Shared\Collection as Orders;
use CG\Order\Shared\Item\Collection as OrderItems;
use CG\Order\Shared\Item\Entity as OrderItem;
use CG\Order\Shared\Label\Collection as OrderLabels;
use CG\Order\Shared\Label\Entity as OrderLabel;
use CG\Order\Shared\ShippableInterface as Order;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\User\Entity as User;

class Exporter implements ExporterInterface
{
    const COUNTRY_NAME_UK = 'United Kingdom';

    protected $serviceMap = [
        ShippingService::FIRST_CLASS => [
            'BPR1' => [ShippingService::ADD_ON_SIGNED_FOR_VALUE],
            'BPL1' => [],
        ],
        ShippingService::SECOND_CLASS => [
            'BPR2' => [ShippingService::ADD_ON_SIGNED_FOR_VALUE],
            'BPL2' => [],
        ],
        ShippingService::TWENTY_FOUR => 'CRL24',
        ShippingService::FORTY_EIGHT => 'CRL48',
        ShippingService::SPECIAL_DELIVERY => [
            'SD6' => ['9am', '£2500', ShippingService::ADD_ON_SIGNED_FOR_VALUE],
            'SD5' => ['9am', '£1000', ShippingService::ADD_ON_SIGNED_FOR_VALUE],
            'SD4' => ['9am', ShippingService::ADD_ON_SIGNED_FOR_VALUE],
            'SD3' => ['£2500', ShippingService::ADD_ON_SIGNED_FOR_VALUE],
            'SD2' => ['£1000', ShippingService::ADD_ON_SIGNED_FOR_VALUE],
            'SD1' => [ShippingService::ADD_ON_SIGNED_FOR_VALUE],
        ],
        ShippingService::FIRST_CLASS_ACCOUNT => 'STL1',
        ShippingService::SECOND_CLASS_ACCOUNT => 'STL2',
        ShippingService::TRACKED_TWENTY_FOUR => 'TPN24',
        ShippingService::TRACKED_FORTY_EIGHT => 'TPS48',
        ShippingService::TRACKED_RETURNS_FORTY_EIGHT => 'TSS',
        ShippingService::INTERNATIONAL_STANDARD => 'OLA',
        ShippingService::INTERNATIONAL_ECONOMY => 'OLS',
        ShippingService::INTERNATIONAL_TRACKED => [
            'OTD' => [ShippingService::ADD_ON_SIGNED_FOR_VALUE, 'extraCompensation'],
            'OTC' => [ShippingService::ADD_ON_SIGNED_FOR_VALUE],
            'OTB' => ['extraCompensation'],
            'OTA' => [],
        ],
        ShippingService::INTERNATIONAL_BUSINESS => [
            'MTE' => [ShippingService::ADD_ON_SIGNED_FOR_VALUE, 'tracked', 'packageTypes' => ['Parcel']],
            'MP9' => [ShippingService::ADD_ON_SIGNED_FOR_VALUE, 'packageTypes' => ['Parcel']],
            'MP7' => ['tracked', 'packageTypes' => ['Parcel']],
            'IE1' => ['packageTypes' => ['Parcel']],
            'IG1' => ['packageTypes' => ['Large letter']],
        ],
        ShippingService::INTERNATIONAL_SIGNED_ON => [
            'OSB' => [ShippingService::ADD_ON_SIGNED_FOR_VALUE, 'extraCompensation'],
            'OSA' => [ShippingService::ADD_ON_SIGNED_FOR_VALUE],
        ],
    ];

    public function exportOrders(
        Orders $orders,
        OrderLabels $orderLabels,
        array $ordersData,
        array $orderParcelsData,
        array $orderItemsData,
        OrganisationUnit $rootOu,
        User $user
    ): ExportDocumentInterface {
        $export = new Export();
        /** @var OrderLabel $orderLabel */
        foreach ($orderLabels as $orderLabel) {
            $this->addParcelDataToExport(
                $export,
                $orders->getById($orderLabel->getOrderId()),
                $orderLabel,
                $ordersData[$orderLabel->getOrderId()] ?? [],
                $orderParcelsData[$orderLabel->getOrderId()] ?? [],
                $orderItemsData[$orderLabel->getOrderId()] ?? [],
                $rootOu,
                $user
            );
        }
        return $export;
    }

    protected function addParcelDataToExport(
        Export $export,
        Order $order,
        OrderLabel $orderLabel,
        array $orderData,
        array $orderParcelsData,
        array $orderItemsData,
        OrganisationUnit $rootOu,
        User $user
    ) {
        [$title, $firstName, $lastName] = $this->parseName($fullName = trim($order->getShippingAddressFullNameForCourier()));

        $addOn = $orderData['addOn'] ?? [];
        $packageType = $orderData['packageType'] ?? '';
        $serviceCode = $this->getServiceCode($orderData['service'] ?? '', $packageType, $addOn);
        $signature = $this->getSignatureSelection($addOn);

        foreach ($orderParcelsData as $orderParcelData) {
            foreach ($orderItemsData as $orderItemId => $orderItemData) {

                /** @var OrderItems $orderItems */
                $orderItems = $order->getItems();

                /** @var OrderItem $orderItem */
                $orderItem = $orderItems->getById($orderItemId);

                if (!isset($orderItem)) {
                    continue;
                }

                $weight = $orderParcelData['weight'] ?? '';
                if ($orderItems->count() > 1) {
                    $weight = $orderItemData['weight'] ?? '';
                }

                $export->addRowData(
                    [
                        'orderReference' => $order->getExternalId(),
                        'specialInstructions' => $orderData['deliveryInstructions'] ?? '',
                        'date' => $orderData['collectionDate'] ?? '',
                        'weight' => $weight,
                        'packageSize' => $packageType,
                        'subTotal' => $order->getTotal() - $order->getShippingPrice(),
                        'shippingCost' => $order->getShippingPrice(),
                        'total' => $order->getTotal(),
                        'currencyCode' => $order->getCurrencyCode(),
                        'serviceCode' => $serviceCode,
                        'signature' => $signature,
                        'customerTitle' => $title,
                        'firstName' => $firstName,
                        'lastName' => $lastName,
                        'fullName' => $fullName,
                        'phone' => $order->getShippingPhoneNumberForCourier(),
                        'email' => $order->getShippingEmailAddressForCourier(),
                        'companyName' => $order->getShippingAddressCompanyNameForCourier(),
                        'addressLine1' => $order->getShippingAddress1ForCourier(),
                        'addressLine2' => $order->getShippingAddress2ForCourier(),
                        'addressLine3' => $order->getShippingAddress3ForCourier(),
                        'city' => $order->getShippingAddressCityForCourier(),
                        'county' => $order->getShippingAddressCountyForCourier(),
                        'postcode' => $order->getShippingAddressPostcodeForCourier(),
                        'country' => $order->getShippingAddressCountryForCourier(),
                        'productSku' => $orderItem->getItemSku(),
                        'customsDescription' => $orderItem->getItemName(),
                        'customsCode' => $orderItemData['harmonisedSystemCode'] ?? '',
                        'countryOfOrigin' => static::COUNTRY_NAME_UK,
                        'quantity' => $orderItem->getItemQuantity(),
                        'unitPrice' => $orderItem->getIndividualItemPrice(),
                    ]
                );
            }
        }
    }

    protected function parseName(string $name): array
    {
        $parsed = preg_match(
            '/^(?:(?<title>Dr|Master|Mr|Mrs|Ms|Miss|Mx)\.?\s*)?(?:(?<firstName>[^\s]+)\s+)?(?<lastName>.*)$/i',
            $name,
            $match
        );
        if (!$parsed) {
            return ['', '', $name];
        }
        return [$match['title'] ?? '', $match['firstName'] ?? '', $match['lastName'] ?? ''];
    }

    protected function getServiceCode(string $service, string $packageType, array $addOns = []): string
    {
        $serviceMap = $this->serviceMap[$service] ?? '';
        if (!is_array($serviceMap)) {
            return $serviceMap;
        }
        foreach ($serviceMap as $serviceCode => $requiredAddOns) {
            $availablePackages = [];
            if (isset($requiredAddOns['packageTypes'])) {
                $availablePackages = $requiredAddOns['packageTypes'];
                unset($requiredAddOns['packageTypes']);
            }

            if (count(array_intersect($requiredAddOns, $addOns)) == count($requiredAddOns)) {
                if (!empty($availablePackages) && !in_array($packageType, $availablePackages)) {
                    continue;
                }
                return $serviceCode;
            }
        }
        return '';
    }

    protected function getSignatureSelection(array $addOn): string
    {
        if (in_array(ShippingService::ADD_ON_SIGNED_FOR_VALUE, $addOn)) {
            return 'y';
        }

        return 'n';
    }
}