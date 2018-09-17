<?php
namespace CG\ShipStation\ShippingService;

use CG\Account\Shared\Entity as ShippingAccount;
use CG\Order\Shared\ShippableInterface as Order;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\ShipStation\Messages\CarrierService;
use CG\ShipStation\Response\Shipping\CarrierServices as CarrierServicesResponse;
use CG\ShipStation\ShippingServiceInterface;
use CG\Stdlib\Exception\Runtime\NotFound;

class Other implements ShippingServiceInterface
{
    /** @var ShippingAccount */
    protected $account;
    /** @var string */
    protected $domesticCountryCode;

    public function __construct(ShippingAccount $account, string $domesticCountryCode)
    {
        $this->account = $account;
        $this->domesticCountryCode = $domesticCountryCode;
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
        if (!isset($this->account->getExternalData()['services']) || !$this->account->getExternalData()['services']) {
            throw new NotFound('No services found for the account "' . $this->account->getId() . '"');
        }

        return CarrierServicesResponse::createFromJson($this->account->getExternalData()['services']);
    }

    public function getShippingServicesForOrder(Order $order)
    {
        $services = [];
        try {
            $response = $this->getAccountShippingServices();
            foreach ($response->getServices() as $service) {
                $carrierService = $service->getCarrierService();
                if (!$this->isServiceApplicableToOrder($carrierService, $order)) {
                    continue;
                }
                $services[$carrierService->getServiceCode()] = $carrierService->getName();
            }
        } catch (NotFound $e) {
            // No services found, leave the services array empty
        }
        return $services;
    }

    protected function isServiceApplicableToOrder($carrierService, $order): bool
    {
        $international = $this->isInternationalOrder($order);
        return ($international && $carrierService->isInternational()) || (!$international && $carrierService->isDomestic());
    }

    protected function isInternationalOrder(Order $order): bool
    {
        return $order->getShippingAddressCountryCodeForCourier() != $this->domesticCountryCode;
    }

    public function doesServiceHaveOptions($service)
    {
        return false;
    }

    public function getOptionsForService($service, $selected = null)
    {
        return [];
    }

    public function getCarrierService(string $serviceCode): CarrierService
    {
        $response = $this->getAccountShippingServices();
        $carrierServiceEntity = $response->getService($serviceCode);
        return $carrierServiceEntity->getCarrierService();
    }
}