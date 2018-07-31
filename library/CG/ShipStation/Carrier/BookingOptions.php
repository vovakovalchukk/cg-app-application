<?php
namespace CG\ShipStation\Carrier;

use CG\Account\Shared\Entity as AccountEntity;
use CG\Channel\Shipping\Provider\BookingOptions\CreateActionDescriptionInterface;
use CG\Channel\Shipping\Provider\BookingOptions\CreateAllActionDescriptionInterface;
use CG\Channel\Shipping\Provider\BookingOptionsInterface;
use CG\NetDespatch\ShippingService\OptionTypes\PackageType;
use CG\Order\Shared\ShippableInterface as OrderEntity;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Product\Detail\Collection as ProductDetailCollection;
use CG\Product\Detail\Entity as ProductDetailEntity;
use CG\ShipStation\PackageType\Collection as PackageTypeCollection;
use CG\ShipStation\PackageType\Entity as PackageTypeEntity;
use CG\ShipStation\PackageType\Service as PackageTypeService;

class BookingOptions implements BookingOptionsInterface, CreateActionDescriptionInterface, CreateAllActionDescriptionInterface
{
    /** @var Service */
    protected $service;
    /** @var PackageTypeService */
    protected $packageTypeService;

    protected $courierActionsMap = [
        'usps-ss' => [
            'create' => 'Purchase label',
            'createAll' => 'Purchase all labels',
        ]
    ];

    public function __construct(Service $service, PackageTypeService $packageTypeService)
    {
        $this->service = $service;
        $this->packageTypeService = $packageTypeService;
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

        $potentialPackageTypes = $this->getPossiblePackageTypesForService($service);
        $packageTypes = $this->restrictPackageTypesByLocalityOfOrder($order, $potentialPackageTypes);

        if (count($productDetails) > 1) {
            $selectedPackage = $this->getMostAppropriatePackageTypeByItemRequirements($productDetails, $potentialPackageTypes);
        }

        return $this->preparePackageTypesForView($packageTypes, $selectedPackage ?? null);
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

    protected function getPossiblePackageTypesForService(string $service): PackageTypeCollection
    {
        return $this->packageTypeService->getPackageTypesForService($service);
    }

    public function restrictPackageTypesByLocalityOfOrder(OrderEntity $order, PackageTypeCollection $packageCollection): PackageTypeCollection
    {
        $countryCode = $order->getShippingAddressCountryCodeForCourier();
        if ($this->isShippingCountryDomestic($countryCode)) {
            return $this->packageTypeService->getDomesticPackages($packageCollection);
        } else {
            return $this->packageTypeService->getInternationalPackages($packageCollection);
        }
    }

    protected function isShippingCountryDomestic($countryCode)
    {
        return ($countryCode == 'US');
    }

    protected function getMostAppropriatePackageTypeByItemRequirements(ProductDetailCollection $productDetails, PackageTypeCollection $packageTypeCollection): ?PackageTypeEntity
    {
        /** @var ProductDetailEntity $product */
        $product = $productDetails->getFirst();

        /** @var PackageTypeEntity $potentialPackageType */
        foreach ($packageTypeCollection as $potentialPackageType) {
            if ($this->packageTypeService->isPackageSuitableForItemWeightAndDimensions($potentialPackageType, $product)) {
                return $potentialPackageType;
            }
        }
        return null;
    }

    protected function preparePackageTypesForView(PackageTypeCollection $packageTypeCollection, ?PackageTypeEntity $selectedPackage): array
    {
        $packageTypesData = [];
        /** @var PackageTypeEntity $packageType */
        foreach ($packageTypeCollection as $packageType) {
            $packageTypesData[$packageType->getCode()] = $packageType->getName();
        }

        if ($selectedPackage !== null) {
            unset($packageTypesData[$selectedPackage->getCode()]);
            $packageTypesData = [$selectedPackage->getCode() => $selectedPackage->getName()] + $packageTypesData;
        }

        return $packageTypesData;
    }

    protected function returnDefaultPackage()
    {
        return [$this->packageTypeService::USPS_DEFAULT_PACKAGE_CODE => $this->packageTypeService::USPS_DEFAULT_PACKAGE_NAME];
    }
}