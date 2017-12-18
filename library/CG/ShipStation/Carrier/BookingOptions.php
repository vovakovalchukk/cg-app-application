<?php
namespace CG\ShipStation\Carrier;

use CG\Account\Shared\Entity as AccountEntity;
use CG\Channel\Shipping\Provider\BookingOptionsInterface;
use CG\Order\Shared\ShippableInterface as OrderEntity;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Product\Detail\Collection as ProductDetailCollection;

class BookingOptions implements BookingOptionsInterface
{
    /** @var Service */
    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function getCarrierBookingOptionsForAccount(AccountEntity $account, $serviceCode = null)
    {
        return $this->service->getCarrierForAccount($account)->getBookingOptions($serviceCode);
    }

    public function addCarrierSpecificDataToListArray(array $data, AccountEntity $account)
    {
        // TODO: Implement addCarrierSpecificDataToListArray() method.
    }

    public function getDataForCarrierBookingOption(
        $option,
        OrderEntity $order,
        AccountEntity $account,
        $service,
        OrganisationUnit $rootOu,
        ProductDetailCollection $productDetails
    ) {
        // TODO: Implement getDataForCarrierBookingOption() method.
    }

    public function isProvidedAccount(AccountEntity $account)
    {
        return $this->service->isProvidedAccount($account);
    }

    public function isProvidedChannel($channel)
    {
        return $this->service->isProvidedChannel($channel);
    }
}