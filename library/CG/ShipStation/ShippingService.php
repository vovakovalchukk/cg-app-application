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

    protected function getAccountShippingServices(): array
    {
        if (!isset($this->account->getExternalData()['services'])) {
            return [];
        }

        $shippingServices = json_decode($this->account->getExternalData()['services']);
        return is_array($shippingServices) ? $shippingServices : [];
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
