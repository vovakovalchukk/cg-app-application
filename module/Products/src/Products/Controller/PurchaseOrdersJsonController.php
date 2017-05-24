<?php
namespace Products\Controller;

use CG\ETag\Exception\Conflict;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Mvc\Controller\AbstractActionController;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;

use CG_UI\View\Prototyper\JsonModelFactory;
use CG\PurchaseOrder\Service as PurchaseOrderService;
use CG\PurchaseOrder\Mapper as PurchaseOrderMapper;
use CG\PurchaseOrder\Filter as PurchaseOrderFilter;
use CG\PurchaseOrder\Collection as PurchaseOrderCollection;
use CG\PurchaseOrder\Item\Service as PurchaseOrderItemService;
use CG\PurchaseOrder\Item\Mapper as PurchaseOrderItemMapper;
use CG\PurchaseOrder\Item\Filter as PurchaseOrderItemFilter;
use CG\PurchaseOrder\Item\Entity as PurchaseOrderItemEntity;
use CG\PurchaseOrder\Item\Collection as PurchaseOrderItemCollection;
use CG\Product\Client\Service as ProductService;
use CG\Product\Filter as ProductFilter;
use CG\Product\Collection as ProductCollection;
use CG\User\ActiveUserInterface;
use CG\Zend\Stdlib\Http\FileResponse;

class PurchaseOrdersJsonController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;

    const ROUTE_LIST = 'AJAX List';
    const ROUTE_COMPLETE = 'AJAX Complete';
    const ROUTE_DOWNLOAD = 'AJAX Download';
    const ROUTE_DELETE = 'AJAX Delete';
    const ROUTE_SAVE = 'AJAX Save';
    const ROUTE_CREATE = 'AJAX Create';

    protected $jsonModelFactory;
    protected $purchaseOrderService;
    protected $purchaseOrderMapper;
    protected $productService;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    protected $purchaseOrderItemService;
    protected $purchaseOrderItemMapper;
    protected $updatedPurchaseOrderItemIds;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        PurchaseOrderService $purchaseOrderService,
        PurchaseOrderMapper $purchaseOrderMapper,
        ProductService $productService,
        ActiveUserInterface $activeUserContainer,
        PurchaseOrderItemService $purchaseOrderItemService,
        PurchaseOrderItemMapper $purchaseOrderItemMapper
    ) {
        $this->jsonModelFactory = $jsonModelFactory;
        $this->purchaseOrderService = $purchaseOrderService;
        $this->purchaseOrderMapper = $purchaseOrderMapper;
        $this->productService = $productService;
        $this->activeUserContainer = $activeUserContainer;
        $this->purchaseOrderItemService = $purchaseOrderItemService;
        $this->purchaseOrderItemMapper = $purchaseOrderItemMapper;
    }

    public function createAction()
    {
        $externalId = $this->params()->fromPost('externalId');
        $products = $this->params()->fromPost('products');

        $purchaseOrder = $this->purchaseOrderMapper->fromArray([
            'externalId' => $externalId,
            'items' => $this->buildPurchaseOrderItemsCollection($products),
        ]);
        $purchaseOrder->setCreated(date('Y-m-d H:i:s'));
        $purchaseOrder = $this->purchaseOrderService->save($purchaseOrder);

        $id = $purchaseOrder->getId();

        return $this->jsonModelFactory->newInstance([
            'success' => true,
            'id' => $id,
        ]);
    }

    public function saveAction()
    {
        $id = $this->params()->fromPost('id');
        $externalId = $this->params()->fromPost('externalId');
        $updatedPurchaseOrderItems = json_decode($this->params()->fromPost('products'), true);
        $purchaseOrder = null;
        $poError = false;

        try {
            $purchaseOrder = $this->purchaseOrderService->fetch($id);
            $purchaseOrder->setExternalId($externalId);
            $this->purchaseOrderService->save($purchaseOrder);
        } catch (NotModified $e) {
            $poError = true;
        }

        $poiError = $this->savePurchaseOrderItems($updatedPurchaseOrderItems, $purchaseOrder);

        $removalError = $this->removePurchaseOrderItems($purchaseOrder);

        if ($poError && $poiError && $removalError) {
            return $this->jsonModelFactory->newInstance([
                'error' => "The purchase order was not modified."
            ]);
        }

        return $this->jsonModelFactory->newInstance([
            'success' => true,
        ]);
    }

    public function deleteAction()
    {
        $id = $this->params()->fromPost('id');
        try {
            $purchaseOrder = $this->purchaseOrderService->fetch($id);

            $this->purchaseOrderService->remove($purchaseOrder);
        } catch (\Exception $e) {
            return $this->jsonModelFactory->newInstance([
                'error' => "A problem occurred when attempting to delete the purchase order. ".$e->getMessage()
            ]);
        }

        return $this->jsonModelFactory->newInstance([
            'success' => true
        ]);
    }

    public function downloadAction()
    {
        $id = $this->params()->fromPost('id');

        try {
            $purchaseOrder = $this->purchaseOrderService->fetch($id);

            $purchaseOrderCsv = $this->purchaseOrderService->convertToCsv($purchaseOrder);
            $fileName = date(DATE_ISO8601) . " purchase_order.csv";

            return new FileResponse('text/csv', $fileName, (string) $purchaseOrderCsv);
        } catch (\Exception $e) {
            return $this->jsonModelFactory->newInstance([
                'error' => "A problem occurred when attempting to download the purchase order. ".$e->getMessage()
            ]);
        }
    }

    public function completeAction()
    {
        $id = $this->params()->fromPost('id');
        try {
            $purchaseOrder = $this->purchaseOrderService->fetch($id);
            $purchaseOrder->setStatus('Complete');

            $this->purchaseOrderService->save($purchaseOrder);
        } catch (NotModified $e) {
            return $this->jsonModelFactory->newInstance([
                'error' => "The purchase order was not modified."
            ]);
        }
        return $this->jsonModelFactory->newInstance([
            'success' => true
        ]);
    }

    public function listAction()
    {
        $ouId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        $filter = (new PurchaseOrderFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setOrganisationUnitId([$ouId]);
        $records = $this->purchaseOrderService->fetchCollectionByFilter($filter);

        return $this->jsonModelFactory->newInstance([
            'list' => $this->hydratePurchaseOrdersWithProducts($records, $ouId),
        ]);
    }

    protected function hydratePurchaseOrdersWithProducts(PurchaseOrderCollection $purchaseOrders, $ouId)
    {
        $allProductSkus = [];
        foreach ($purchaseOrders as $purchaseOrder) {
            foreach ($purchaseOrder->getItems() as $purchaseOrderItem) {
                $allProductSkus[$purchaseOrderItem->getSku()] = $purchaseOrder->getId();
            }
        }

        $filter = (new ProductFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setOrganisationUnitId([$ouId])
            ->setReplaceVariationWithParent(true)
            ->setSku(array_keys($allProductSkus));
        $products = $this->productService->fetchCollectionByFilter($filter);

        $purchaseOrderWithProducts = [];
        foreach ($purchaseOrders as $purchaseOrder) {
            $purchaseOrderWithProduct = $purchaseOrder->toArray();
            foreach ($purchaseOrder->getItems() as $purchaseOrderItem) {
                $purchaseOrderWithProduct['items'][] = $this->getItemData($products, $purchaseOrderItem);
            }
            $purchaseOrderWithProducts[] = $purchaseOrderWithProduct;
        }
        return $purchaseOrderWithProducts;
    }

    protected function getItemData($products, $purchaseOrderItem)  {
        $purchaseOrderItemArray = $purchaseOrderItem->toArray();
        $item = null;

        $productsBySku = $products->getBy('sku', $purchaseOrderItem->getSku());
        if (count($productsBySku) === 0) {
            foreach ($products as $product) {
                $variations = $product->getVariations();
                $variationsBySku = $variations->getBy('sku', $purchaseOrderItem->getSku());
                if (count($variationsBySku)) {
                    $item = $product;
                    break;
                }
            }
        } else {
            $productsBySku->rewind();
            $item = $productsBySku->current();
        }
        $purchaseOrderItemArray['product'] = $item->toArray();
        if ($item->getVariations()) {
            foreach ($item->getVariations() as $variation) {
                $variationArray = $variation->toArray();
                foreach ($variation->getImages() as $image) {
                    $variationArray['images'][] = $image->toArray();
                }
                foreach ($variation->getStock()->getLocations() as $location) {
                    $variationArray['stock']['locations'][] = $location->toArray();
                }
                $purchaseOrderItemArray['product']['variations'][] = $variationArray;
            }
        }
        foreach ($item->getImages() as $image) {
            $purchaseOrderItemArray['product']['images'][] = $image->toArray();
        }
        return $purchaseOrderItemArray;
    }

    protected function savePurchaseOrderItems($updatedPurchaseOrderItems, $purchaseOrder)
    {

        $filter = (new PurchaseOrderItemFilter())
            ->setPage(1)
            ->setLimit('all')
            ->setPurchaseOrderId([$purchaseOrder->getId()]);
        try {
            $purchaseOrderItems = $this->purchaseOrderItemService->fetchCollectionByFilter($filter);
        } catch (NotFound $e) {
            $purchaseOrderItems = [];
        }
        $error = false;
        foreach ($updatedPurchaseOrderItems as &$updatedPurchaseOrderItem) {
            try {
                $item = null;
                if (isset($updatedPurchaseOrderItem['id']) && count($purchaseOrderItems)) {
                    $this->updatedPurchaseOrderItemIds[] = $updatedPurchaseOrderItem['id'];
                    $item = $purchaseOrderItems->getById($updatedPurchaseOrderItem['id']);
                }

                if (isset($item)) {
                    $item->setSku($updatedPurchaseOrderItem['sku']);
                    $item->setQuantity($updatedPurchaseOrderItem['quantity']);
                } else {
                    $updatedPurchaseOrderItem['purchaseOrderId'] = $purchaseOrder->getId();
                    $updatedPurchaseOrderItem['organisationUnitId'] = $purchaseOrder->getOrganisationUnitId();
                    $item = $this->purchaseOrderItemMapper->fromArray($updatedPurchaseOrderItem);
                }
                $this->purchaseOrderItemService->save($item);
                $error = false;
            } catch (NotModified $e) {
                $error = true;
            } catch (Conflict $e) {
                $error = true;
            }
        }
        return $error;
    }

    protected function removePurchaseOrderItems($purchaseOrder)
    {
        $purchaseOrderItems = $purchaseOrder->getItems();
        foreach ($purchaseOrderItems as $purchaseOrderItem) {
            try {
                if (! in_array($purchaseOrderItem->getId(), $this->updatedPurchaseOrderItemIds)) {
                    $this->purchaseOrderItemService->remove($purchaseOrderItem);
                }
            } catch (NotModified $e) {
                $removalError = true;
            }
        }
    }
}
