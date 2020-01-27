<?php
namespace Products\PurchaseOrder;

use CG\Di\Di;
use CG\PurchaseOrder\Entity as PurchaseOrder;
use CG\PurchaseOrder\Service as PurchaseOrderService;
use CG\Stdlib\Exception\Runtime\NotFound;
use Products\PurchaseOrder\CsvExport\ProductDetail as ProductDetailCsvExport;

class Service
{
    protected const COLUMN_SUPPLIER = 'Supplier';

    protected const ADDITIONAL_FIELDS_MAP = [
        self::COLUMN_SUPPLIER => ProductDetailCsvExport::class
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

    protected function fetchAdditionalDataForPurchaseOrder(PurchaseOrder $purchaseOrder): array
    {
        $additionalFields = [];
        foreach (static::ADDITIONAL_FIELDS_MAP as $column => $className) {
            $csvExportClass = $this->createCsvExportClass($className);
            $additionalFields[$column] = $csvExportClass->fetchAdditionalData($purchaseOrder);
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
