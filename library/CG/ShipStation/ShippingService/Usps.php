<?php
namespace CG\ShipStation\ShippingService;

use CG\Channel\Shipping\ServicesInterface as ShippingServiceInterface;
use CG\Order\Shared\ShippableInterface as Order;

class Usps implements ShippingServiceInterface
{

    public function getShippingServices()
    {
        return [
            'usps_first_class_mail' => 'USPS First Class Mail',
            'usps_media_mail' => 'USPS Media Mail',
            'usps_parcel_select' => 'USPS Parcel Select Ground',
            'usps_priority_mail' => 'USPS Priority Mail',
            'usps_priority_mail_express' => 'USPS Priority Mail Express',
            /* We're not supporting international services for any ShipStation couriers for now
            'usps_first_class_mail_international' => 'USPS First Class Mail Intl',
            'usps_priority_mail_international' => 'USPS Priority Mail Intl',
            'usps_priority_mail_express_international' => 'USPS Priority Mail Express Intl', */
        ];
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