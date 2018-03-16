<?php
namespace CG\CourierExport\RoyalMailClickDrop;

use CG\Channel\Shipping\Provider\Service\ExportDocumentInterface;
use CG\CourierExport\ExporterInterface;
use CG\Order\Shared\Collection as Orders;
use CG\Order\Shared\Label\Collection as OrderLabels;
use CG\Order\Shared\Label\Entity as OrderLabel;
use CG\Order\Shared\ShippableInterface as Order;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\User\Entity as User;

class Exporter implements ExporterInterface
{
    protected $serviceMap = [
        ShippingService::FIRST_CLASS => [
            'BPR1' => ['signedFor'],
            'BPL1' => [],
        ],
        ShippingService::SECOND_CLASS => [
            'BPR2' => ['signedFor'],
            'BPL2' => [],
        ],
        ShippingService::TWENTY_FOUR => 'CRL24',
        ShippingService::FORTY_EIGHT => 'CRL48',
        ShippingService::SPECIAL_DELIVERY => [
            'SD6' => ['9am', '£2500'],
            'SD5' => ['9am', '£1000'],
            'SD4' => ['9am'],
            'SD3' => ['£2500'],
            'SD2' => ['£1000'],
            'SD1' => [],
        ],
        ShippingService::FIRST_CLASS_ACCOUNT => 'STL1',
        ShippingService::SECOND_CLASS_ACCOUNT => 'STL2',
        ShippingService::INTERNATIONAL_STANDARD => 'OLA',
        ShippingService::INTERNATIONAL_ECONOMY => 'OLS',
        ShippingService::INTERNATIONAL_ECONOMY => 'OLS',
        ShippingService::INTERNATIONAL_TRACKED => [
            'OTD' => ['signedFor', 'extraCompensation'],
            'OTC' => ['signedFor'],
            'OTB' => ['extraCompensation'],
            'OTA' => [],
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
        foreach ($orderParcelsData as $orderParcelData) {
            $export->addRowData(
                [
                    'orderReference' => $order->getExternalId(),
                    'specialInstructions' => $orderData['deliveryInstructions'] ?? '',
                    'date' => $orderData['collectionDate'] ?? '',
                    'weight' => $orderParcelData['weight'] ?? '',
                    'packageSize' => $orderData['packageType'] ?? '',
                    'subTotal' => $order->getTotal() - $order->getShippingPrice(),
                    'shippingCost' => $order->getShippingPrice(),
                    'total' => $order->getTotal(),
                    'currencyCode' => $order->getCurrencyCode(),
                    'serviceCode' => $this->getServiceCode($orderData['service'] ?? '', $orderData['addOn'] ?? []),
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
                ]
            );
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

    protected function getServiceCode(string $service, array $addOns = []): string
    {
        $serviceMap = $this->serviceMap[$service] ?? '';
        if (!is_array($serviceMap)) {
            return $serviceMap;
        }
        foreach ($serviceMap as $serviceCode => $requiredAddOns) {
            if (count(array_intersect($requiredAddOns, $addOns)) == count($requiredAddOns)) {
                return $serviceCode;
            }
        }
        return '';
    }
}