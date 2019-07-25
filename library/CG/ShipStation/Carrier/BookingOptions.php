<?php
namespace CG\ShipStation\Carrier;

use CG\Account\Shared\Entity as AccountEntity;
use CG\Channel\Shipping\Provider\BookingOptions\CreateActionDescriptionInterface;
use CG\Channel\Shipping\Provider\BookingOptions\CreateAllActionDescriptionInterface;
use CG\Channel\Shipping\Provider\BookingOptionsInterface;
use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\ShippableInterface as OrderEntity;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Product\Detail\Collection as ProductDetailCollection;
use CG\ShipStation\Carrier\BookingOption\Factory as BookingOptionFactory;
use CG\ShipStation\Carrier\CarrierSpecificData\Factory as CarrierSpecificDataFactory;

class BookingOptions implements BookingOptionsInterface, CreateActionDescriptionInterface, CreateAllActionDescriptionInterface
{
    /** @var Service */
    protected $service;
    /** @var BookingOptionFactory */
    protected $bookingOptionFactory;
    /** @var CarrierSpecificDataFactory */
    protected $carrierSpecificDataFactory;

    protected $courierActionsMap = [
        'usps-ss' => [
            'create' => 'Purchase label',
            'createAll' => 'Purchase all labels',
        ]
    ];

    public function __construct(Service $service, BookingOptionFactory $bookingOptionFactory, CarrierSpecificDataFactory $carrierSpecificDataFactory)
    {
        $this->service = $service;
        $this->bookingOptionFactory = $bookingOptionFactory;
        $this->carrierSpecificDataFactory = $carrierSpecificDataFactory;
    }

    public function getCarrierBookingOptionsForAccount(AccountEntity $account, $serviceCode = null)
    {
        return $this->service->getCarrierForAccount($account)->getBookingOptions();
    }

    public function addCarrierSpecificDataToListArray(
        array $data,
        AccountEntity $account,
        OrganisationUnit $rootOu,
        OrderCollection $orders,
        ProductDetailCollection $productDetails
    ) {
        $carrierSpecificDataProvider = ($this->carrierSpecificDataFactory)($account->getChannel());
        $data = $carrierSpecificDataProvider->getCarrierSpecificData($data, $account);
        return $data;
    }

    public function getDataForCarrierBookingOption(
        $option,
        OrderEntity $order,
        AccountEntity $account,
        $service,
        OrganisationUnit $rootOu,
        ProductDetailCollection $productDetails
    ) {
        $bookingOptionProvider = ($this->bookingOptionFactory)($account->getChannel());
        return $bookingOptionProvider->getOptionsDataForOption($option, $order, $account, $service, $rootOu, $productDetails);
    }

    public function isProvidedAccount(AccountEntity $account)
    {
        return $this->service->isProvidedAccount($account);
    }

    public function isProvidedChannel($channel)
    {
        return $this->service->isProvidedChannel($channel);
    }

    /**
     * @return string What to show for the 'create' action buttons
     */
    public function getCreateActionDescription(AccountEntity $shippingAccount): string
    {
        $channel = $shippingAccount->getChannel();
        if (isset($this->courierActionsMap[$channel], $this->courierActionsMap[$channel]['create'])) {
            return $this->courierActionsMap[$channel]['create'];
        }
        return 'Create label';
    }

    /**
     * @return string What to show for the 'create all' action button
     */
    public function getCreateAllActionDescription(AccountEntity $shippingAccount): string
    {
        $channel = $shippingAccount->getChannel();
        if (isset($this->courierActionsMap[$channel], $this->courierActionsMap[$channel]['create'])) {
            return $this->courierActionsMap[$channel]['createAll'];
        }
        return 'Create all labels';
    }
}