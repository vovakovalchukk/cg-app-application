<?php
namespace Products\PurchaseOrder;

use CG\PurchaseOrder\Entity as PurchaseOrder;

interface CsvExportInterface
{
    public function fetchAdditionalData(PurchaseOrder $purchaseOrder): array;
}
