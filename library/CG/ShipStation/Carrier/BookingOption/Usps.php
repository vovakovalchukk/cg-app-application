<?php
namespace CG\ShipStation\Carrier\BookingOption;

use CG\Account\Shared\Entity as AccountEntity;
use CG\Order\Shared\ShippableInterface as OrderEntity;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Product\Detail\Collection as ProductDetailCollection;
use CG\Product\Detail\Entity as ProductDetailEntity;
use CG\ShipStation\Carrier\BookingOptionInterface;
use CG\ShipStation\PackageType\Usps\Collection as PackageTypeCollection;
use CG\ShipStation\PackageType\Usps\Entity as PackageTypeEntity;
use CG\ShipStation\PackageType\Usps\Service as PackageTypeService;

class Usps implements BookingOptionInterface
{
    /** @var PackageTypeService */
    protected $packageTypeService;

    public function __construct(PackageTypeService $packageTypeService)
    {
        $this->packageTypeService = $packageTypeService;
    }

    public function getOptionsDataForOption(
        $option,
        OrderEntity $order,
        AccountEntity $account,
        $service,
        OrganisationUnit $rootOu,
        ProductDetailCollection $productDetails
    ): ?array
    {
        if ($option !== 'packageTypes') {
            return [];
        }

        $potentialPackageTypes = $this->getPossiblePackageTypesForService($service);
        $packageTypes = $this->restrictPackageTypesByLocalityOfOrder($order, $potentialPackageTypes);

        // If we only have one item, we can attempt to determine the appropriate package to select by default
        if (count($productDetails) == 1) {
            $selectedPackage = $this->getMostAppropriatePackageTypeByItemRequirements($productDetails, $potentialPackageTypes);
        }

        return $this->preparePackageTypesForView($packageTypes, $selectedPackage ?? null);
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
}