<?php
namespace Products\PurchaseOrder\CsvExport;

use CG\Product\Detail\Collection as ProductDetailCollection;
use CG\Product\Detail\Entity as ProductDetailEntity;
use CG\Product\Detail\Filter as ProductDetailFilter;
use CG\Product\Detail\Service as ProductDetailService;
use CG\PurchaseOrder\Entity as PurchaseOrder;
use CG\PurchaseOrder\Item\Entity as PurchaseOrderItem;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Supplier\Collection as SupplierCollection;
use CG\Supplier\Entity as Supplier;
use CG\Supplier\Filter as SupplierFilter;
use CG\Supplier\Service as SupplierService;
use Products\PurchaseOrder\CsvExportInterface;

class ProductDetail implements CsvExportInterface
{
    protected const PRODUCT_DETAIL_FETCH_LIMIT = 200;

    /** @var ProductDetailService */
    protected $productDetailService;
    /** @var SupplierService */
    protected $supplierService;

    public function __construct(ProductDetailService $productDetailService, SupplierService $supplierService)
    {
        $this->productDetailService = $productDetailService;
        $this->supplierService = $supplierService;
    }

    public function fetchAdditionalData(PurchaseOrder $purchaseOrder): array
    {
        $skus = $this->getUniqueSkusForPurchaseOrder($purchaseOrder);
        $productDetailCollection = $this->fetchProductDetails($purchaseOrder->getOrganisationUnitId(), $skus);
        return $this->buildSkusToDetailsMap($productDetailCollection, $purchaseOrder->getOrganisationUnitId());
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

    protected function fetchProductDetails(int $ouId, array $skus): ProductDetailCollection
    {
        $page = 0;
        $productDetailFilter = $this->buildProductDetailFilter($ouId, $skus);
        $productDetailCollection = new ProductDetailCollection(ProductDetail::class, __METHOD__);

        do {
            $productDetailFilter->setPage(++$page);
            try {
                /** @var ProductDetailCollection $collection */
                $collection = $this->productDetailService->fetchCollectionByFilter($productDetailFilter);
                $productDetailCollection->attachAll($collection);
            } catch (NotFound $e) {
                return $productDetailCollection;
            }
        } while ($productDetailCollection->getTotal() > $page * static::PRODUCT_DETAIL_FETCH_LIMIT);

        return $productDetailCollection;
    }

    protected function buildProductDetailFilter(int $ouId, array $skus): ProductDetailFilter
    {
        return (new ProductDetailFilter())
            ->setSku($skus)
            ->setOrganisationUnitId([$ouId])
            ->setLimit(static::PRODUCT_DETAIL_FETCH_LIMIT);
    }

    protected function buildSkusToDetailsMap(ProductDetailCollection $collection, int $ouId): array
    {
        $skuToSupplierMap = $this->buildSkuToSupplierMap($collection, $ouId);

        $skuToDetailsMap = [];
        /** @var ProductDetailEntity $productDetail */
        foreach ($collection as $productDetail) {
            $skuToDetailsMap[$productDetail->getSku()] = array_merge($productDetail->toArray(), [
                'supplier' => $skuToSupplierMap[$productDetail->getSku()] ?? ''
            ]);
        }

        return $skuToDetailsMap;
    }

    protected function buildSkuToSupplierMap(ProductDetailCollection $collection, int $ouId): array
    {
        $skuToSupplierIdMap = $this->buildSkuToSupplierIdMap($collection);
        $supplierIds = $this->getUniqueSupplierIds($skuToSupplierIdMap);
        $suppliers = $this->fetchSuppliers($ouId, $supplierIds);

        $skuToSupplierNameMap = [];
        foreach ($skuToSupplierIdMap as $sku => $supplierId) {
            /** @var Supplier $supplier */
            $supplier = $suppliers->getById($supplierId);
            $skuToSupplierNameMap[$sku] = $supplier ? $supplier->getName() : '';
        }

        return $skuToSupplierNameMap;
    }

    protected function buildSkuToSupplierIdMap(ProductDetailCollection $collection): array
    {
        $skuToSupplierMap = [];
        /** @var ProductDetailEntity $productDetail */
        foreach ($collection as $productDetail) {
            $skuToSupplierMap[$productDetail->getSku()] = $productDetail->getSupplierId();
        }
        return $skuToSupplierMap;
    }

    protected function getUniqueSupplierIds(array $skuToSupplierMap): array
    {
        return array_unique(array_values($skuToSupplierMap));
    }

    protected function fetchSuppliers(int $ouId, array $supplierIds): SupplierCollection
    {
        $supplierFilter = (new SupplierFilter())
            ->setOrganisationUnitId([$ouId])
            ->setPage(1)
            ->setLimit('all')
            ->setId($supplierIds);

        try {
            return $this->supplierService->fetchCollectionByFilter($supplierFilter);
        } catch (NotFound $exception) {
            return new SupplierCollection(Supplier::class, __METHOD__);
        }
    }
}
