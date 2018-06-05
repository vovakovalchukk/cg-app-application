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

    const ADD_ON_SIGNED_FOR_VALUE = 'signedFor';

    protected $services = [
        self::FIRST_CLASS => self::FIRST_CLASS,
        self::SECOND_CLASS => self::SECOND_CLASS,
        self::TWENTY_FOUR => self::TWENTY_FOUR,
        self::FORTY_EIGHT => self::FORTY_EIGHT,
        self::SPECIAL_DELIVERY => self::SPECIAL_DELIVERY,
        self::FIRST_CLASS_ACCOUNT => self::FIRST_CLASS_ACCOUNT,
        self::SECOND_CLASS_ACCOUNT => self::SECOND_CLASS_ACCOUNT,
        self::INTERNATIONAL_STANDARD => self::INTERNATIONAL_STANDARD,
        self::INTERNATIONAL_ECONOMY => self::INTERNATIONAL_ECONOMY,
        self::INTERNATIONAL_TRACKED => self::INTERNATIONAL_TRACKED,
    ];

    public function getShippingServices()
    {
        return $this->services;
    }

    public function getShippingServicesForOrder(Order $order)
    {
        return $this->getShippingServices();
    }

    public function doesServiceHaveOptions($service)
    {
        return false;
    }

    public function getOptionsForService($service, $selected = null)
    {
        return [];
    }
}