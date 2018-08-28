<?php
namespace CG\ShipStation\Carrier\CarrierSpecificData;

use CG\Account\Shared\Entity as Account;
use CG\Product\Detail\Mapper as ProductDetailMapper;
use CG\ShipStation\Carrier\CarrierSpecificDataInterface;
use CG\ShipStation\PackageType\RoyalMail\Entity as PackageType;
use CG\ShipStation\PackageType\RoyalMail\Service as PackageTypeService;

class RoyalMail implements CarrierSpecificDataInterface
{
    /** @var PackageTypeService */
    protected $packageTypeService;
    /** @var ProductDetailMapper */
    protected $productDetailMapper;

    public function __construct(PackageTypeService $packageTypeService, ProductDetailMapper $productDetailMapper)
    {
        $this->packageTypeService = $packageTypeService;
        $this->productDetailMapper = $productDetailMapper;
    }

    public function getCarrierSpecificData(array $data, Account $account): ?array
    {
        foreach ($data as &$row) {
            $countryCode = $row['shippingCountryCode'];
            $packageType = $this->getPackageTypeForListRow($row, $account);
            $row['packageType'] = $packageType ? $packageType->getCode() : null;
            $packageTypes = $this->packageTypeService->getForCountryCode($countryCode);
            $row['packageTypes'] = $packageTypes->toOptionsArrayOfArrays($packageType);
        }
        return $data;
    }

    protected function getPackageTypeForListRow(array &$row, Account $account): ?PackageType
    {
        if (isset($row['packageType']) && $row['packageType']) {
            return $row['packageType'];
        }
        if (!isset($row['weight'], $row['height'], $row['width'], $row['length']) ||
            !$row['weight'] || !$row['height'] || !$row['width'] || !$row['length']
        ) {
            return $this->packageTypeService->getDefault($row['shippingCountryCode']);
        }

        $productDetail = $this->productDetailMapper->fromArray([
            'organisationUnitId' => $account->getOrganisationUnitId(),
            'sku' => $row['itemSku'],
            'weight' => $row['weight'],
            'height' => $row['height'],
            'width' => $row['width'],
            'length' => $row['length'],
        ]);
        return $this->packageTypeService->getForProductDetail($productDetail, $row['shippingCountryCode']);
    }
}