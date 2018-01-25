<?php
namespace CG\ShipStation;

use CG\Account\Shared\Entity as ShippingAccount;
use CG\Channel\Shipping\ServicesInterface as ShippingServiceInterface;
use CG\Order\Shared\ShippableInterface as Order;
use CG\ShipStation\Response\Shipping\CarrierServices as CarrierServicesResponse;
use CG\Stdlib\Exception\Runtime\NotFound;

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
        try {
            $response = $this->getAccountShippingServices();
            foreach ($response->getServices() as $service) {
                $services[$service->getCarrierService()->getServiceCode()] = $service->getCarrierService()->getName();
            }
        } catch (NotFound $e) {
            // No services found, leave the services array empty
        }
        return $services;
    }

    protected function getAccountShippingServices(): CarrierServicesResponse
    {
        if (!isset($this->account->getExternalData()['services'])) {
            throw new NotFound('No services found for the account "' . $this->account->getId() . '"');
        }

        return CarrierServicesResponse::createFromJson($this->account->getExternalData()['services']);
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
