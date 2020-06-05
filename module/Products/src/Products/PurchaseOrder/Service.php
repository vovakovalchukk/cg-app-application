<?php
namespace Products\PurchaseOrder;

use CG\Di\Di;
use CG\PurchaseOrder\Entity as PurchaseOrder;
use CG\PurchaseOrder\Item\Entity as PurchaseOrderItem;
use CG\PurchaseOrder\Service as PurchaseOrderService;
use CG\Stdlib\Exception\Runtime\NotFound;
use Products\PurchaseOrder\CsvExport\Product as ProductCsvExport;
use Products\PurchaseOrder\CsvExport\ProductDetail as ProductDetailCsvExport;

class Service
{
    protected const COLUMN_NAME_PRODUCT_NAME = 'Product Name';
    protected const COLUMN_NAME_SUPPLIER = 'Supplier';
    protected const COLUMN_NAME_COST = 'Cost Price';
    protected const COLUMN_NAME_EAN = 'EAN';
    protected const COLUMN_NAME_UPC = 'UPC';
    protected const COLUMN_NAME_BRAND = 'Brand';
    protected const COLUMN_NAME_MPN = 'MPN';
    protected const COLUMN_NAME_ASIN = 'ASIN';
    protected const COLUMN_NAME_GTIN = 'GTIN';
    protected const COLUMN_NAME_ISBN = 'ISBN';
    protected const COLUMN_NAME_BARCODE_NOT_APPLICABLE = 'Barcode Not Applicable';

    protected const ADDITIONAL_FIELDS_MAP = [
        ProductCsvExport::class => [
            self::COLUMN_NAME_PRODUCT_NAME => 'name'
        ],
        ProductDetailCsvExport::class => [
            self::COLUMN_NAME_SUPPLIER => 'supplier',
            self::COLUMN_NAME_COST => 'cost',
            self::COLUMN_NAME_EAN => 'ean',
            self::COLUMN_NAME_UPC => 'upc',
            self::COLUMN_NAME_BRAND => 'brand',
            self::COLUMN_NAME_MPN => 'mpn',
            self::COLUMN_NAME_ASIN => 'asin',
            self::COLUMN_NAME_GTIN => 'gtin',
            self::COLUMN_NAME_ISBN => 'isbn',
            self::COLUMN_NAME_BARCODE_NOT_APPLICABLE => 'barcodeNotApplicable'
        ]
    ];

    /** @var PurchaseOrderService */
    protected $purchaseOrderService;
    /** @var Di */
    protected $di;

    public function __construct(PurchaseOrderService $purchaseOrderService, Di $di)
    {
        $this->purchaseOrderService = $purchaseOrderService;
        $this->di = $di;
    }

    public function exportPurchaseOrderAsCsv(int $purchaseOrderId): string
    {
        try {
            /** @var PurchaseOrder $purchaseOrder */
            $purchaseOrder = $this->purchaseOrderService->fetch($purchaseOrderId);
        } catch (NotFound $exception) {
            return '';
        }

        $additionalData = $this->fetchAdditionalDataForPurchaseOrder($purchaseOrder);
        return $this->purchaseOrderService->convertToCsv($purchaseOrder, $additionalData);
    }

    protected function getUniqueSkusForPurchaseOrder(PurchaseOrder $purchaseOrder): array
    {
        $skus = [];
        /** @var PurchaseOrderItem $item */
        foreach ($purchaseOrder->getItems() as $item) {
            $skus[$item->getSku()] = $item->getSku();
        }
        return array_values($skus);
    }

    protected function fetchAdditionalDataForPurchaseOrder(PurchaseOrder $purchaseOrder): array
    {
        $uniqueSkus = $this->getUniqueSkusForPurchaseOrder($purchaseOrder);
        $additionalFields = [];
        foreach (static::ADDITIONAL_FIELDS_MAP as $className => $columnMap) {
            $csvExportClass = $this->createCsvExportClass($className);
            $additionalData = $csvExportClass->fetchAdditionalData($purchaseOrder, $uniqueSkus);
            foreach ($columnMap as $columnName => $dataKey) {
                $additionalFields[$columnName] = array_column($additionalData, $dataKey, 'sku');
            }
        }
        return $additionalFields;
    }

    protected function createCsvExportClass(string $className): CsvExportInterface
    {
        if (!class_exists($className)) {
            throw new \InvalidArgumentException('The requested CSV Export class:' . $className . 'doesn\'t exist.');
        }

        return $this->di->newInstance($className);
    }
}
