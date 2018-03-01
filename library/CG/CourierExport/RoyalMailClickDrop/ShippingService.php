<?php
namespace CG\CourierExport\RoyalMailClickDrop;

use CG\Channel\Shipping\ServicesInterface;
use CG\Order\Shared\ShippableInterface as Order;

class ShippingService implements ServicesInterface
{
    protected $services = [
        'Royal Mail 1st Class' => [
            'inputType' => 'multiselect',
            'options' => [
                ['title' => 'Signed For', 'value' => 'signedFor'],
            ],
        ],
        'Royal Mail 2nd Class' => [
            'inputType' => 'multiselect',
            'options' => [
                ['title' => 'Signed For', 'value' => 'signedFor'],
            ],
        ],
        'Royal Mail 24' => false,
        'Royal Mail 48' => false,
        'Special Delivery' => [
            'inputType' => 'multiselect',
            'options' => [
                ['title' => 'Guaranteed by 1pm', 'value' => '1pm', 'excludes' => '9am'],
                ['title' => 'Guaranteed by 9am', 'value' => '9am', 'excludes' => '1pm'],
                ['title' => 'Up to £500 Compensation', 'value' => '£500', 'excludes' => '£1000,£2500'],
                ['title' => 'Up to £1000 Compensation', 'value' => '£1000', 'excludes' => '£500,£2500'],
                ['title' => 'Up to £2500 Compensation', 'value' => '£2500', 'excludes' => '£500,£1000'],
            ],
        ],
        '1st Class Account Mail' => false,
        '2nd Class Account Mail' => false,
        'International Standard On Account' => false,
        'International Economy On Account' => false,
        'International Tracked On Account' => [
            'inputType' => 'multiselect',
            'options' => [
                ['title' => 'Extra Compensation', 'value' => 'extraCompensation'],
                ['title' => 'Signed For', 'value' => 'signedFor'],
            ],
        ],
    ];

    public function getShippingServices()
    {
        return array_keys($this->services);
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

        $selectedOptions = array_fill_keys($selected ? str_getcsv($selected) : [], true);
        foreach ($options['options'] ?? [] as &$option) {
            $option['selected'] = isset($selectedOptions[$option['value']]);
        }

        return $options;
    }
}