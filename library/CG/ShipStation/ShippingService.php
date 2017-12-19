<?php
namespace CG\ShipStation;

use CG\Account\Shared\Entity as ShippingAccount;
use CG\Channel\Shipping\ServicesInterface as ShippingServiceInterface;
use CG\Order\Shared\ShippableInterface as Order;

class ShippingService implements ShippingServiceInterface
{
    /** @var ShippingAccount */
    protected $account;

    public function __construct(ShippingAccount $account)
    {
        $this->account = $account;
    }

    public function getShippingServices()
    {
        $services = [];
        foreach ($this->getAccountShippingServices() as $service) {
            $services[$service['serviceCode']] = $service['name'];
        }
        return $services;
    }

    protected function getAccountShippingServices(): \Generator
    {
        if (!isset($this->account->getExternalData()['services'])) {
            return [];
        }

        $services = json_decode($this->account->getExternalData()['services']);
        if (!is_array($services)) {
            return [];
        }

        foreach ($services as $service) {
            if (isset($service['carrierService']) && is_array($service['carrierService'])) {
                yield $service['carrierService'];
            }
        }
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
