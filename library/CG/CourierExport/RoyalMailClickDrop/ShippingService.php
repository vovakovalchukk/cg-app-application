<?php
namespace CG\CourierExport\RoyalMailClickDrop;

use CG\Channel\Shipping\ServicesInterface;
use CG\Order\Shared\ShippableInterface as Order;

class ShippingService implements ServicesInterface
{
    const FIRST_CLASS = 'Royal Mail 1st Class';
    const SECOND_CLASS = 'Royal Mail 2nd Class';
    const TWENTY_FOUR = 'Royal Mail 24';
    const FORTY_EIGHT = 'Royal Mail 48';
    const SPECIAL_DELIVERY = 'Special Delivery';
    const FIRST_CLASS_ACCOUNT = '1st Class Account Mail';
    const SECOND_CLASS_ACCOUNT = '2nd Class Account Mail';
    const INTERNATIONAL_STANDARD = 'International Standard On Account';
    const INTERNATIONAL_ECONOMY = 'International Economy On Account';
    const INTERNATIONAL_TRACKED = 'International Tracked On Account';

    protected $services = [
        self::FIRST_CLASS => [
            'inputType' => 'multiselect',
            'options' => [
                ['title' => 'Signed For', 'value' => 'signedFor'],
            ],
        ],
        self::SECOND_CLASS => [
            'inputType' => 'multiselect',
            'options' => [
                ['title' => 'Signed For', 'value' => 'signedFor'],
            ],
        ],
        self::TWENTY_FOUR => false,
        self::FORTY_EIGHT => false,
        self::SPECIAL_DELIVERY => [
            'inputType' => 'multiselect',
            'options' => [
                ['title' => 'Guaranteed by 1pm', 'value' => '1pm', 'excludes' => '9am'],
                ['title' => 'Guaranteed by 9am', 'value' => '9am', 'excludes' => '1pm'],
                ['title' => 'Up to £500 Compensation', 'value' => '£500', 'excludes' => '£1000,£2500'],
                ['title' => 'Up to £1000 Compensation', 'value' => '£1000', 'excludes' => '£500,£2500'],
                ['title' => 'Up to £2500 Compensation', 'value' => '£2500', 'excludes' => '£500,£1000'],
            ],
            'defaultSelection' => ['1pm', '£500'],
        ],
        self::FIRST_CLASS_ACCOUNT => false,
        self::SECOND_CLASS_ACCOUNT => false,
        self::INTERNATIONAL_STANDARD => false,
        self::INTERNATIONAL_ECONOMY => false,
        self::INTERNATIONAL_TRACKED => [
            'inputType' => 'multiselect',
            'options' => [
                ['title' => 'Extra Compensation', 'value' => 'extraCompensation'],
                ['title' => 'Signed For', 'value' => 'signedFor'],
            ],
        ],
    ];

    public function getShippingServices()
    {
        $services = array_keys($this->services);
        return array_combine($services, $services);
    }

    public function getShippingServicesForOrder(Order $order)
    {
        return $this->getShippingServices();
    }

    public function doesServiceHaveOptions($service)
    {
        return isset($this->services[$service]) && is_array($this->services[$service]);
    }

    public function getOptionsForService($service, $selected = null)
    {
        if (!$this->doesServiceHaveOptions($service)) {
            return [];
        }

        $options = $this->services[$service];

        $selectedOptions = array_fill_keys($selected ? str_getcsv($selected) : ($options['defaultSelection'] ?? []), true);
        foreach ($options['options'] ?? [] as &$option) {
            $option['selected'] = isset($selectedOptions[$option['value']]);
        }

        return $options;
    }
}