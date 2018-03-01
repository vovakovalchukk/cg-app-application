<?php
namespace CG\CourierExport;

use CG\Account\Shared\Entity as Account;
use CG\Channel\Shipping\ServicesInterface;
use CG\Order\Shared\ShippableInterface as Order;
use CG\Stdlib\Exception\Runtime\NotFound;
use function CG\Stdlib\hyphenToClassname;

class ShippingService implements ServicesInterface
{
    /** @var Account */
    protected $account;

    public function __construct(Account $account)
    {
        $this->account = $account;
    }

    protected function getShippingService(): ServicesInterface
    {
        $class = __NAMESPACE__ . '\\' . hyphenToClassname($this->account->getChannel()) . '\\ShippingService';
        if (!class_exists($class) || !is_a($class, ServicesInterface::class, true)) {
            throw new NotFound(sprintf('Unsupported account "%s"', $this->account->getChannel()));
        }
        return new $class();
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