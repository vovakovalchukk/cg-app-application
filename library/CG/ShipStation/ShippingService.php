<?php
namespace CG\ShipStation;

use CG\Account\Shared\Entity as ShippingAccount;
use CG\Channel\Shipping\ServicesInterface as ShippingServiceInterface;
use CG\Order\Shared\ShippableInterface as Order;
use CG\ShipStation\ShippingService\Factory;
use CG\ShipStation\ShippingService\RequiresSignatureInterface;
use CG\Stdlib\Exception\Runtime\NotFound;

class ShippingService implements ShippingServiceInterface
{
    /** @var ShippingAccount */
    protected $account;
    /** @var Factory */
    protected $factory;

    /** @var ShippingServiceInterface */
    protected $accountShippingService;

    public function __construct(ShippingAccount $account, Factory $factory)
    {
        $this->account = $account;
        $this->factory = $factory;
    }

    public function getShippingServices()
    {
        return $this->getAccountShippingService()->getShippingServices();
    }

    protected function getAccountShippingService(): ShippingServiceInterface
    {
        if ($this->accountShippingService) {
            return $this->accountShippingService;
        }
        $this->accountShippingService = ($this->factory)($this->account);
        return $this->accountShippingService;
    }

    public function getShippingServicesForOrder(Order $order)
    {
        return $this->getAccountShippingService()->getShippingServicesForOrder($order);
    }

    public function doesServiceHaveOptions($service)
    {
        return $this->getAccountShippingService()->doesServiceHaveOptions($service);
    }

    public function getOptionsForService($service, $selected = null)
    {
        return $this->getAccountShippingService()->getOptionsForService($service, $selected);
    }

    public function doesServiceRequireSignature(string $service): bool
    {
        $accountShippingService = $this->getAccountShippingService();
        if (!$accountShippingService instanceof RequiresSignatureInterface) {
            return false;
        }
        return $accountShippingService->doesServiceRequireSignature($service);
    }
}
