<?php
namespace CG\ShipStation\Carrier\BookingOption;

use CG\Account\Shared\Entity as AccountEntity;
use CG\Order\Shared\ShippableInterface as OrderEntity;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Product\Detail\Collection as ProductDetailCollection;
use CG\ShipStation\Carrier\BookingOptionInterface;
use CG\ShipStation\PackageType\RoyalMail\Service as PackageTypeService;

class RoyalMail implements BookingOptionInterface
{
    /** @var PackageTypeService */
    protected $packageTypeService;

    protected $optionMethods = [
        'packageTypes' => 'getPackageTypeOptions',
    ];

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
    ): ?array {
        if (!isset($this->optionMethods[$option])) {
            return [];
        }
        $method = $this->optionMethods[$option];
        return $this->{$method}($order, $account, $service, $rootOu, $productDetails);
    }

    protected function getPackageTypeOptions(
        OrderEntity $order,
        AccountEntity $account,
        $service,
        OrganisationUnit $rootOu,
        ProductDetailCollection $productDetails
    ): array {
        $countryCode = $order->getShippingAddressCountryCodeForCourier();
        $packageTypes = $this->packageTypeService->getForCountryCode($countryCode);
        $selectedPackageType = $this->packageTypeService->getForProductDetails($productDetails, $countryCode);
        return $packageTypes->toOptionsArrayOfArrays($selectedPackageType);
    }
}