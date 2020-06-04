<?php
namespace Products\Controller;

use Application\Controller\AbstractJsonController;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\Product\Detail\Collection as ProductDetailsCollection;
use CG\Product\Detail\Entity as ProductDetails;
use CG\Product\Detail\Filter as ProductDetailsFilter;
use CG\Product\Detail\Service as ProductDetailsService;
use CG\PurchaseOrder\Collection as PurchaseOrderCollection;
use CG\PurchaseOrder\Entity as PurchaseOrder;
use CG\PurchaseOrder\Mapper as PurchaseOrderMapper;
use CG\PurchaseOrder\Service as PurchaseOrderService;
use CG\PurchaseOrder\Status as PurchaseOrderStatus;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LogTrait;
use CG\Stock\Entity as Stock;
use CG\Stock\Filter as StockFilter;
use CG\Stock\Service as StockService;
use CG\User\ActiveUserInterface;
use CG\Zend\Stdlib\Http\FileResponse;
use CG_UI\View\Prototyper\JsonModelFactory;
use Products\PurchaseOrder\Service as PurchaseOrderLocalService;

class PurchaseOrdersJsonController extends AbstractJsonController
{
    use LogTrait;

    const ROUTE_LIST = 'AJAX List';
    const ROUTE_COMPLETE = 'AJAX Complete';
    const ROUTE_DOWNLOAD = 'AJAX Download';
    const ROUTE_DELETE = 'AJAX Delete';
    const ROUTE_SAVE = 'AJAX Save';
    const ROUTE_CREATE = 'AJAX Create';
    const FETCH_LOW_STOCK_PRODUCTS = 'Fetch low stock products';
    const ROUTE_FETCH_SKUS_BY_SUPPLIER = 'AJAX Fetch Skus for supplier';

    const STOCK_FETCH_LIMIT = 300;
    const PRODUCT_DETAILS_FETCH_LIMIT = 300;

    /** @var PurchaseOrderService */
    protected $purchaseOrderService;
    /** @var PurchaseOrderMapper */
    protected $purchaseOrderMapper;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var StockService */
    protected $stockService;
    /** @var ProductDetailsService */
    protected $productDetailsService;
    /** @var PurchaseOrderLocalService */
    protected $purchaseOrderLocalService;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        PurchaseOrderService $purchaseOrderService,
        PurchaseOrderMapper $purchaseOrderMapper,
        ActiveUserInterface $activeUserContainer,
        StockService $stockService,
        ProductDetailsService $productDetailsService,
        PurchaseOrderLocalService $purchaseOrderLocalService
    ) {
        parent::__construct($jsonModelFactory);
        $this->purchaseOrderService = $purchaseOrderService;
        $this->purchaseOrderMapper = $purchaseOrderMapper;
        $this->activeUserContainer = $activeUserContainer;
        $this->stockService = $stockService;
        $this->productDetailsService = $productDetailsService;
        $this->purchaseOrderLocalService = $purchaseOrderLocalService;
    }

    public function createAction()
    {
        $externalId = $this->params()->fromPost('externalId');
        $purchaseOrderItems = json_decode($this->params()->fromPost('purchaseOrderItems'), true);

        try {
            /** @var PurchaseOrder $purchaseOrder */
            $purchaseOrder = $this->purchaseOrderMapper->fromArray([
                'externalId' => $externalId,
                'organisationUnitId' => $this->activeUserContainer->getActiveUserRootOrganisationUnitId(),
                'userId' => $this->activeUserContainer->getActiveUser()->getId(),
                'status' => PurchaseOrderStatus::IN_PROGRESS,
                'created' => date('Y-m-d H:i:s'),
            ]);
            $purchaseOrder = $this->purchaseOrderService->save($purchaseOrder, $purchaseOrderItems);
        } catch (NotModified $e) {
            // No-op
        } catch (\Exception $e) {
            return $this->buildErrorResponse("A problem occurred when attempting to save the purchase order. Please try again.");
        }

        return $this->buildSuccessResponse(['id' => $purchaseOrder->getId()]);
    }

    public function saveAction()
    {
        $id = $this->params()->fromPost('id');
        $externalId = $this->params()->fromPost('externalId');
        $updatedPurchaseOrderItems = json_decode($this->params()->fromPost('purchaseOrderItems'), true);

        try {
            /** @var PurchaseOrder $purchaseOrder */
            $purchaseOrder = $this->purchaseOrderService->fetch($id);
            $purchaseOrder->setExternalId($externalId);
            $this->purchaseOrderService->save($purchaseOrder, $updatedPurchaseOrderItems);
        } catch (NotModified $e) {
            // No-op
        } catch (\Exception $e) {
            return $this->buildErrorResponse("A problem occurred when attempting to save the purchase order. Please try again.");
        }

        return $this->buildSuccessResponse();
    }

    public function deleteAction()
    {
        $id = $this->params()->fromPost('id');
        try {
            /** @var PurchaseOrder $purchaseOrder */
            $purchaseOrder = $this->purchaseOrderService->fetch($id);
            $this->purchaseOrderService->remove($purchaseOrder);
        } catch (\Exception $e) {
            $this->buildErrorResponse("A problem occurred when attempting to delete the purchase order.");
        }

        return $this->buildSuccessResponse();
    }

    public function downloadAction()
    {
        $id = $this->params()->fromPost('id');

        try {
            $purchaseOrderCsv = $this->purchaseOrderLocalService->exportPurchaseOrderAsCsv($id);
            $fileName = date('Y-m-d hi') . " purchase_order.csv";

            return new FileResponse('text/csv', $fileName, (string) $purchaseOrderCsv);
        } catch (\Exception $e) {
            return $this->buildErrorResponse("A problem occurred when attempting to download the purchase order.");
        }
    }

    public function completeAction()
    {
        try {
            $this->purchaseOrderService->markAsComplete($this->params()->fromPost('id'));
        } catch (\Exception $e) {
            return $this->buildErrorResponse("A problem occurred when attempting to save the purchase order. Please try again.");
        }
        return $this->buildSuccessResponse();
    }

    public function listAction()
    {
        try {
            $ouId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
            /** @var PurchaseOrderCollection $records */
            $records = $this->purchaseOrderService->fetchAllForOu($ouId);
        } catch (NotFound $e) {
            return $this->buildResponse(['list' => []]);
        } catch (\Exception $e) {
            return $this->buildErrorResponse("A problem occurred while retrieving purchase orders.");
        }

        return $this->buildResponse([
            'list' => $this->purchaseOrderMapper->hydratePurchaseOrdersWithProducts($records, $ouId)
        ]);
    }

    public function fetchLowStockProductsAction()
    {
        $page = 0;

        $filter = (new StockFilter())
            ->setLimit(static::STOCK_FETCH_LIMIT)
            ->setOrganisationUnitId([$this->activeUserContainer->getActiveUserRootOrganisationUnitId()])
            ->setLowStockThresholdTriggered(true);

        $productSkus = [];

        do {
            $filter->setPage(++$page);
            try {
                $stockCollection = $this->stockService->fetchCollectionByFilter($filter);
                /** @var Stock $stock */
                foreach ($stockCollection as $stock) {
                    $productSkus[] = $stock->getSku();
                }
            } catch (NotFound $e) {
                break;
            }
        } while ($stockCollection->getTotal() > $page * static::STOCK_FETCH_LIMIT);

        return $this->buildResponse([
            'skus' => $productSkus
        ]);
    }

    public function fetchProductSkusForSupplierAction()
    {
        $supplierId = intval($this->params()->fromPost('supplierId', 0));
        $filter = $this->buildProductDetailsFilterBySupplier($supplierId);

        return $this->buildResponse([
            'skus' => array_values($this->fetchProductDetailSkusByFilter($filter))
        ]);
    }

    protected function buildProductDetailsFilterBySupplier(int $supplierId): ProductDetailsFilter
    {
        return (new ProductDetailsFilter())
            ->setLimit(static::PRODUCT_DETAILS_FETCH_LIMIT)
            ->setOrganisationUnitId([$this->activeUserContainer->getActiveUserRootOrganisationUnitId()])
            ->setSupplierId([$supplierId]);
    }

    protected function fetchProductDetailSkusByFilter(ProductDetailsFilter $filter): array
    {
        $page = 0;
        $skus = [];

        do {
            $filter->setPage(++$page);
            try {
                /** @var ProductDetailsCollection $productDetailsCollection */
                $productDetailsCollection = $this->productDetailsService->fetchCollectionByFilter($filter);
                /** @var ProductDetails $productDetails */
                foreach ($productDetailsCollection as $productDetails) {
                    $skus[$productDetails->getSku()] = $productDetails->getSku();
                }
            } catch (NotFound $e) {
                break;
            }
        } while ($productDetailsCollection->getTotal() > $page * static::PRODUCT_DETAILS_FETCH_LIMIT);

        return $skus;
    }
}
