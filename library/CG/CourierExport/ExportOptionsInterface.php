<?php
namespace CG\CourierExport;

use CG\Order\Shared\ShippableInterface as Order;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\Product\Detail\Collection as ProductDetails;

interface ExportOptionsInterface
{
    /**
     * @return array ['{optionName}' => bool] for the courier booking options e.g. 'signature', 'deliveryInstructions', etc
     */
    public function getDefaultExportOptions($serviceCode = null): array;

    /**
     * Given the array of rows for the courier booking table add in any carrier-specific data as required
     * @return array The modified list array
     */
    public function addCarrierSpecificDataToListArray(array $data): array;

    /**
     * Given a particular carrier booking option and supporting info return data to populate it with
     * @return mixed Something that can be sent back over AJAX e.g. string or array
     */
    public function getDataForCarrierExportOption(
        $option,
        Order $order,
        $service,
        OrganisationUnit $rootOu,
        ProductDetails $productDetails
    );
}