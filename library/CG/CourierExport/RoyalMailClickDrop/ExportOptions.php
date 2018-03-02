<?php
namespace CG\CourierExport\RoyalMailClickDrop;

use CG\CourierExport\ExportOptionsInterface;
use CG\Order\Shared\ShippableInterface as Order;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Product\Detail\Collection as ProductDetails;

class ExportOptions implements ExportOptionsInterface
{
    protected $exportOptions = [
        'packageTypes' => [['title' => 'Letter'], ['title' => 'Large letter'], ['title' => 'Parcel']],
    ];

    public function getExportOptions($serviceCode = null): array
    {
        return ['packageType', 'collectionDate', 'deliveryInstructions', 'weight'];
    }

    public function addCarrierSpecificDataToListArray(array $data): array
    {
        foreach ($data as &$row) {
            $row = array_merge($row, $this->exportOptions);
        }
        return $data;
    }

    public function getDataForCarrierExportOption(
        $option,
        Order $order,
        $service,
        OrganisationUnit $rootOu,
        ProductDetails $productDetails
    ) {
        if (!isset($this->exportOptions[$option])) {
            return '';
        }
        if (!is_array($this->exportOptions[$option])) {
            return $this->exportOptions[$option];
        }
        return array_column($this->exportOptions[$option], 'title');
    }
}