<?php
namespace CG\CourierExport;

use CG\Account\Shared\Entity as Account;
use CG\Channel\Shipping\ServicesInterface;
use CG\Order\Shared\ShippableInterface as Order;

class ShippingService implements ServicesInterface
{
    /** @var Factory */
    protected $factory;
    /** @var Account */
    protected $account;

    public function __construct(Factory $factory, Account $account)
    {
        $this->factory = $factory;
        $this->account = $account;
    }

    protected function getShippingService(): ServicesInterface
    {
        return $this->factory->getShippingServiceForAccount($this->account);
    }

    public function getShippingServices()
    {
        return $this->getShippingService()->getShippingServices();
    }

    public function getShippingServicesForOrder(Order $order)
    {
        return $this->getShippingService()->getShippingServicesForOrder($order);
    }

    public function doesServiceHaveOptions($service)
    {
        return $this->getShippingService()->doesServiceHaveOptions($service);
    }

    public function getOptionsForService($service, $selected = null)
    {
        return $this->getShippingService()->getOptionsForService($service, $selected);
    }
}