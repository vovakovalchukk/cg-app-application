<?php
namespace CG\ShipStation;

use CG\Account\Shared\Entity as ShippingAccount;
use CG\Channel\Shipping\ServicesInterface as ShippingServiceInterface;
use CG\Order\Shared\ShippableInterface as Order;
use CG\ShipStation\ShippingService\Factory;
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
        // This dummy data and code will be replaced and refactored in TAC-108
        if ($this->account->getChannel() == 'usps-ss') {
            return [
                'usps_first_class_mail' => 'USPS First Class Mail',
                'usps_media_mail' => 'USPS Media Mail',
                'usps_parcel_select' => 'USPS Parcel Select Ground',
            ];
        }

        $services = [];
        try {
            $response = $this->getAccountShippingServices();
            foreach ($response->getServices() as $service) {
                // We are not supporting international for now. If we add it later remove this check.
                if ($service->getCarrierService()->isInternational()) {
                    continue;
                }
                $services[$service->getCarrierService()->getServiceCode()] = $service->getCarrierService()->getName();
            }
        } catch (NotFound $e) {
            // No services found, leave the services array empty
        }
        return $services;
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
}
