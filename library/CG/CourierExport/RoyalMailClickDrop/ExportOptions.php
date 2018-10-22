<?php
namespace CG\CourierExport\RoyalMailClickDrop;

use CG\CourierExport\ExportOptionsInterface;
use CG\Order\Shared\ShippableInterface as Order;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Product\Detail\Collection as ProductDetails;

class ExportOptions implements ExportOptionsInterface
{
    protected $defaultExportOptions = [
        'packageTypes' => [
            'Letter' => ['title' => 'Letter', 'value' => 'Letter'],
            'Large letter' => ['title' => 'Large letter', 'value' => 'Large letter'],
            'Parcel' => ['title' => 'Parcel', 'value' => 'Parcel'],
        ],
    ];
    protected $serviceExportOptions = [
        ShippingService::FIRST_CLASS => [
            'addOns' => [
                ['title' => 'Signed For', 'value' => ShippingService::ADD_ON_SIGNED_FOR_VALUE],
            ],
        ],
        ShippingService::SECOND_CLASS => [
            'addOns' => [
                ['title' => 'Signed For', 'value' => ShippingService::ADD_ON_SIGNED_FOR_VALUE],
            ],
        ],
        ShippingService::TWENTY_FOUR => [
            'addOns' => [
                ['title' => 'Signed For', 'value' => ShippingService::ADD_ON_SIGNED_FOR_VALUE],
            ],
        ],
        ShippingService::FORTY_EIGHT => [
            'addOns' => [
                ['title' => 'Signed For', 'value' => ShippingService::ADD_ON_SIGNED_FOR_VALUE],
            ],
        ],
        ShippingService::SPECIAL_DELIVERY => [
            'addOns' => [
                ['title' => 'Guaranteed by 1pm', 'value' => '1pm', 'excludes' => '9am', 'selected' => true],
                ['title' => 'Guaranteed by 9am', 'value' => '9am', 'excludes' => '1pm'],
                ['title' => 'Up to £500 Compensation', 'value' => '£500', 'excludes' => '£1000,£2500', 'selected' => true],
                ['title' => 'Up to £1000 Compensation', 'value' => '£1000', 'excludes' => '£500,£2500'],
                ['title' => 'Up to £2500 Compensation', 'value' => '£2500', 'excludes' => '£500,£1000'],
                ['title' => 'Signed For', 'value' => ShippingService::ADD_ON_SIGNED_FOR_VALUE, 'selected' => true],
            ],
        ],
        ShippingService::FIRST_CLASS_ACCOUNT => [
            'addOns' => [
                ['title' => 'Signed For', 'value' => ShippingService::ADD_ON_SIGNED_FOR_VALUE],
            ],
        ],
        ShippingService::SECOND_CLASS_ACCOUNT => [
            'addOns' => [
                ['title' => 'Signed For', 'value' => ShippingService::ADD_ON_SIGNED_FOR_VALUE],
            ],
        ],
        ShippingService::TRACKED_TWENTY_FOUR => [
            'addOns' => [
                ['title' => 'Signed For', 'value' => ShippingService::ADD_ON_SIGNED_FOR_VALUE],
            ],
        ],
        ShippingService::TRACKED_FORTY_EIGHT => [
            'addOns' => [
                ['title' => 'Signed For', 'value' => ShippingService::ADD_ON_SIGNED_FOR_VALUE],
            ],
        ],
        ShippingService::TRACKED_RETURNS_FORTY_EIGHT => [
            'addOns' => [
                ['title' => 'Signed For', 'value' => ShippingService::ADD_ON_SIGNED_FOR_VALUE],
            ],
        ],
        ShippingService::INTERNATIONAL_TRACKED => [
            'addOns' => [
                ['title' => 'Signed For', 'value' => ShippingService::ADD_ON_SIGNED_FOR_VALUE],
                ['title' => 'Extra Compensation', 'value' => 'extraCompensation'],
            ],
        ],
        ShippingService::INTERNATIONAL_BUSINESS => [
            'packageTypes' => [
                'Large letter' => ['title' => 'Large letter', 'value' => 'Large letter'],
                'Parcel' => ['title' => 'Parcel', 'value' => 'Parcel'],
            ],
            'addOns' => [
                ['title' => 'Signed For', 'value' => ShippingService::ADD_ON_SIGNED_FOR_VALUE],
                ['title' => 'Tracked', 'value' => 'tracked'],
            ],
        ],
        ShippingService::INTERNATIONAL_SIGNED_ON => [
            'addOns' => [
                ['title' => 'Signed For', 'value' => ShippingService::ADD_ON_SIGNED_FOR_VALUE],
                ['title' => 'Extra Compensation', 'value' => 'extraCompensation'],
            ],
        ],
    ];

    public function getDefaultExportOptions($serviceCode = null): array
    {
        return ['packageType', 'addOns', 'collectionDate', 'deliveryInstructions', 'weight'];
    }

    public function addCarrierSpecificDataToListArray(array $data): array
    {
        foreach ($data as &$row) {
            if ($row['actionRow'] ?? false) {
                $row = array_merge($row, $this->defaultExportOptions);
                if (isset($this->serviceExportOptions[$row['service']])) {
                    $row = array_merge($row, $this->serviceExportOptions[$row['service'] ?? '']);
                }
                $row['deliveryInstructionsRequired'] = true;
            }
        }
        return $data;
    }

    public function getDataForCarrierExportOption(
        $option,
        Order $order,
        $service,
        OrganisationUnit $rootOu,
        ProductDetails $productDetails
    ) {
        return $this->serviceExportOptions[$service][$option] ?? $this->defaultExportOptions[$option] ?? '';
    }
}