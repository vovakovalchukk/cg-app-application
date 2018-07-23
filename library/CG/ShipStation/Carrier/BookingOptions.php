<?php
namespace CG\ShipStation\Carrier;

use CG\Account\Shared\Entity as AccountEntity;
use CG\Channel\Shipping\Provider\BookingOptions\CreateActionDescriptionInterface;
use CG\Channel\Shipping\Provider\BookingOptions\CreateAllActionDescriptionInterface;
use CG\Channel\Shipping\Provider\BookingOptionsInterface;
use CG\Order\Shared\Item\Collection as OrderCollection;
use CG\Order\Shared\ShippableInterface as OrderEntity;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Product\Detail\Collection as ProductDetailCollection;

class BookingOptions implements BookingOptionsInterface, CreateActionDescriptionInterface, CreateAllActionDescriptionInterface
{
    /** @var Service */
    protected $service;

    protected $courierActionsMap = [
        'usps-ss' => [
            'create' => 'Purchase label',
            'createAll' => 'Purchase all labels',
        ]
    ];

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function getCarrierBookingOptionsForAccount(AccountEntity $account, $serviceCode = null)
    {
        return $this->service->getCarrierForAccount($account)->getBookingOptions();
    }

    public function addCarrierSpecificDataToListArray(array $data, AccountEntity $account)
    {
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
        if ($account->getChannel() !== 'usps-ss' || $option != 'packageTypes') {
            return [];
        }

        return [
            'Letter' => [
                'height' => 0.5,
                'length' => 9.5,
                'weight' => 70,
                'width' => 12.5,
            ],
            'Large Envelope' => [
                'weight' => 70,
                'length' => 35.3,
                'width' => 25,
                'height' => 2.5,
            ],
            'Package' => [
                'weight' => 70,
                'length' => 61,
                'width' => 46,
                'height' => 46,
            ],
            'Flat Rate Envelope' => [

            ],
            'Flat Rate Padded Envelope' => [

            ],
            'Legal Flat Rate Envelope' => [

            ],
            'Small Flat Rate Box' => [

            ],
            'Medium Flat Rate Box' => [

            ],
            'Large Flat Rate Box' => [

            ],
            'Regional Rate Box A' => [

            ],
            'Regional Rate Box B' => [

            ]
        ];
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
        return 'Create all label';
    }

    protected function getPackageTypeForOrder(OrderEntity $order, $service, ProductDetailCollection $productDetails)
    {
        /** @var OrderCollection $items */
        $items = $order->getItems();
        $items->rewind();
        $item = $items->current();
        // No easy way to figure this out for multiple items
        if ($items->count() > 1 || $item->getItemQuantity() > 1 || $productDetails->count() == 0) {
            // We need to default to something here
        }
        $productDetails->rewind();
        $productDetail = $productDetails->current();
        $data = [
            'service' => $service,
            'shippingCountryCode' => $order->getShippingAddressCountryCodeForCourier(),
            'weight' => $productDetail->getWeight(),
            'height' => $productDetail->getHeight(),
            'width' => $productDetail->getWidth(),
            'length' => $productDetail->getLength(),
        ];
        return $this->getPackageTypeForListRow($data);
    }

    protected function getPackageTypeForListRow(array &$row)
    {
        if (isset($row['packageType']) && $row['packageType']) {
            return $row['packageType'];
        }
        if (!isset($row['service']) ||
            !isset($row['weight'], $row['height'], $row['width'], $row['length']) ||
            !$row['weight'] || !$row['height'] || !$row['width'] || !$row['length']
        ) {
            // Do something default
        }

        // Domestic and International package type dimensions are different
        if ($this->isListRowDomestic($row)) {
            $packageType = DomesticPackageType::getForMeasurements($row['weight'], $row['length'], $row['width'], $row['height']);
        } else {
            $packageType = InternationalPackageType::getForMeasurements($row['weight'], $row['length'], $row['width'], $row['height']);
        }

        return $this->ensurePackageTypeIsAvailableForListRow($row, $packageType);
    }
}