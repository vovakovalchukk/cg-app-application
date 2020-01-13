<?php
namespace Products\Product\Supplier;

use CG\Http\Exception\Exception3xx\NotModified;
use CG\Product\Client\Service as ProductService;
use CG\Product\Detail\Entity as ProductDetail;
use CG\Product\Detail\Mapper as ProductDetailMapper;
use CG\Product\Detail\Service as ProductDetailService;
use CG\Product\Entity as Product;
use CG\Stdlib\Exception\Runtime\Conflict;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Supplier\Collection as SupplierCollection;
use CG\Supplier\Entity as Supplier;
use CG\Supplier\Filter as SupplierFilter;
use CG\Supplier\Mapper as SupplierMapper;
use CG\Supplier\Service as SupplierService;
use CG\User\ActiveUserInterface;

class Service implements LoggerAwareInterface
{
    use LogTrait;

    protected const MAX_SAVE_ATTEMPTS = 2;

    protected const LOG_CODE = 'SupplierService';
    protected const LOG_MESSAGE_SUPPLIER_VARIATION_SAVE_FAILED = 'Could not save the supplier ID %s on variation porduct with ID %s';

    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var SupplierService */
    protected $supplierService;
    /** @var SupplierMapper */
    protected $supplierMapper;
    /** @var ProductService */
    protected $productService;
    /** @var ProductDetailService */
    protected $productDetailService;
    /** @var ProductDetailMapper */
    protected $productDetailMapper;

    public function __construct(
        ActiveUserInterface $activeUserContainer,
        SupplierService $supplierService,
        SupplierMapper $supplierMapper,
        ProductService $productService,
        ProductDetailService $productDetailService,
        ProductDetailMapper $productDetailMapper
    ) {
        $this->activeUserContainer = $activeUserContainer;
        $this->supplierService = $supplierService;
        $this->supplierMapper = $supplierMapper;
        $this->productService = $productService;
        $this->productDetailService = $productDetailService;
        $this->productDetailMapper = $productDetailMapper;
    }

    public function getSupplierOptions(): array
    {
        $suppliers = $this->fetchSuppliersForActiveOu();
        return $this->suppliersToOptions($suppliers);
    }

    protected function fetchSuppliersForActiveOu(): ?SupplierCollection
    {
        try {
            $filter = (new SupplierFilter())
                ->setLimit('all')
                ->setPage(1)
                ->setOrganisationUnitId([$this->activeUserContainer->getActiveUserRootOrganisationUnitId()]);
            return $this->supplierService->fetchCollectionByFilter($filter);
        } catch (NotFound $e) {
            return null;
        }
    }

    protected function suppliersToOptions(?SupplierCollection $suppliers): array
    {
        $options = [];
        if ($suppliers == null) {
            return $options;
        }
        /** @var Supplier $supplier */
        foreach ($suppliers as $supplier) {
            $options[$supplier->getId()] = $supplier->getName();
        }
        return $options;
    }

    public function saveProductSupplier(
        int $productId,
        int $supplierId
    ): void {
        /** @var Product $product */
        $product = $this->productService->fetch($productId);
        if ($product->isParent()) {
            $this->saveSupplierForParentProduct($product, $supplierId);
            return;
        }

        $this->saveSupplierOnProductDetails($productId, $supplierId);
    }

    protected function saveSupplierOnProductDetails(int $productId, int $supplierId): void
    {
        $productDetail = $this->fetchProductDetailFromProductId($productId);
        for ($attempt = 1; $attempt <= static::MAX_SAVE_ATTEMPTS; $attempt++) {
            try {
                $productDetail->setSupplierId($supplierId);
                $this->productDetailService->save($productDetail);
            } catch (NotModified $e) {
                return;
            } catch (Conflict $e) {
                $productDetail = $this->productDetailService->fetch($productDetail->getId());
                continue;
            }
        }
        // We haven't returned, must have run out of attempts
        throw $e;
    }

    protected function saveSupplierForParentProduct(Product $product, $supplierId): void
    {
        foreach ($product->getVariationIds() as $variationId) {
            try {
                $this->saveSupplierOnProductDetails($variationId, $supplierId);
            } catch (\Throwable $e) {
                $this->logWarningException($e, static::LOG_MESSAGE_SUPPLIER_VARIATION_SAVE_FAILED, [$supplierId, $variationId]);
            }
        }
    }

    protected function fetchProductDetailFromProductId(int $productId): ProductDetail
    {
        $product = $this->productService->fetch($productId);
        return $this->fetchProductDetailFromOuAndSku($product->getOrganisationUnitId(), $product->getSku());
    }

    protected function fetchProductDetailFromOuAndSku(int $organisationUnitId, string $sku): ProductDetail
    {
        try {
            return $this->productDetailService->fetchDetailByOuAndSku($organisationUnitId, $sku);
        } catch (NotFound $e) {
            return $this->productDetailMapper->fromArray(['organisationUnitId' => $organisationUnitId, 'sku' => $sku]);
        }
    }

    public function createAndSaveProductSupplier(int $productId, string $supplierName): int
    {
        $supplier = $this->createSupplier($supplierName);
        $this->saveProductSupplier($productId, $supplier->getId());
        return $supplier->getId();
    }

    protected function createSupplier(string $name): Supplier
    {
        $supplier = $this->supplierMapper->fromArray([
            'organisationUnitId' => $this->activeUserContainer->getActiveUserRootOrganisationUnitId(),
            'name' => $name
        ]);
        return $this->supplierMapper->fromHal(
            $this->supplierService->save($supplier)
        );
    }
}