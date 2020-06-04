<?php
namespace Products\PurchaseOrder\CsvExport;

use CG\Product\Client\Service as ProductService;
use CG\Product\Collection as ProductCollection;
use CG\Product\Filter as ProductFilter;
use CG\PurchaseOrder\Entity as PurchaseOrder;
use CG\Stdlib\Exception\Runtime\NotFound;
use Products\PurchaseOrder\CsvExportInterface;

class Product implements CsvExportInterface
{
    /** @var ProductService */
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function fetchAdditionalData(PurchaseOrder $purchaseOrder, array $uniqueSkus): array
    {
        try {
            /** @var ProductCollection $products */
            $products = $this->productService->fetchCollectionByFilter((new ProductFilter())
                ->setLimit('all')
                ->setPage(1)
                ->setSku($uniqueSkus)
                ->setOrganisationUnitId([$purchaseOrder->getOrganisationUnitId()])
            );
        } catch (NotFound $exception) {
            return [];
        }

        return $products->toArray();
    }
}
