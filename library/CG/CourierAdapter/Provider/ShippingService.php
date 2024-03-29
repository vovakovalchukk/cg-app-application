<?php
namespace CG\CourierAdapter\Provider;

use CG\Account\Shared\Entity as AccountEntity;
use CG\Channel\Shipping\ServicesInterface as ShippingServiceInterface;
use CG\CourierAdapter\DeliveryServiceInterface;
use CG\CourierAdapter\Provider\Implementation\Service as AdapterImplementationService;
use CG\CourierAdapter\Provider\Account\Mapper as CAAccountMapper;
use CG\Order\Shared\ShippableInterface as Order;

class ShippingService implements ShippingServiceInterface
{
    /** @var AccountEntity */
    protected $account;
    /** @var AdapterImplementationService */
    protected $adapterImplementationService;
    /** @var CAAccountMapper */
    protected $caAccountMapper;

    /** @var array */
    protected $shippingServices;

    public function __construct(
        AccountEntity $account,
        AdapterImplementationService $adapterImplementationService,
        CAAccountMapper $caAccountMapper
    ) {
        $this->setAccount($account)
            ->setAdapterImplementationService($adapterImplementationService)
            ->setCAAccountMapper($caAccountMapper);
    }

    /**
     * return array [{value} => {title}]
     */
    public function getShippingServices()
    {
        if ($this->shippingServices !== null) {
            return $this->shippingServices;
        }
        $courierInstance = $this->adapterImplementationService->getAdapterImplementationCourierInstanceForAccount($this->account);
        $caAccount = $this->caAccountMapper->fromOHAccount($this->account);
        $deliveryServices = $courierInstance->fetchDeliveryServicesForAccount($caAccount);

        $this->shippingServices = $this->getDeliveryServicesAsOptionsArray($deliveryServices);
        return $this->shippingServices;
    }

    /**
     * return array [{value} => {title}]
     */
    public function getShippingServicesForOrder(Order $order)
    {
        $courierInstance = $this->adapterImplementationService->getAdapterImplementationCourierInstanceForAccount($this->account);
        $caAccount = $this->caAccountMapper->fromOHAccount($this->account);
        $deliveryServices = $courierInstance->fetchDeliveryServicesForAccountAndCountry(
            $caAccount, $order->getShippingAddressCountryCodeForCourier()
        );
        return $this->getDeliveryServicesAsOptionsArray($deliveryServices);
    }

    protected function getDeliveryServicesAsOptionsArray(array $deliveryServices): array
    {
        $optionsArray = [];
        /** @var DeliveryServiceInterface $deliveryService */
        foreach ($deliveryServices as $deliveryService) {
            $optionsArray[$deliveryService->getReference()] = $deliveryService->getDisplayName();
        }
        return $optionsArray;
    }

    /**
     * @return bool
     */
    public function doesServiceHaveOptions($service)
    {
        return false;
    }

    /**
     * @return array
     */
    public function getOptionsForService($service, $selected = null)
    {
        return [];
    }

    protected function setAccount(AccountEntity $account)
    {
        $this->account = $account;
        return $this;
    }

    protected function setAdapterImplementationService(AdapterImplementationService $adapterImplementationService)
    {
        $this->adapterImplementationService = $adapterImplementationService;
        return $this;
    }

    protected function setCAAccountMapper(CAAccountMapper $caAccountMapper)
    {
        $this->caAccountMapper = $caAccountMapper;
        return $this;
    }
}
