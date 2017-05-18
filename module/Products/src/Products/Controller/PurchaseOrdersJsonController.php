<?php
namespace Products\Controller;

use CG\ETag\Exception\NotModified;
use Zend\Mvc\Controller\AbstractActionController;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;

use CG_UI\View\Prototyper\JsonModelFactory;
use CG\PurchaseOrder\Entity as PurchaseOrderEntity;
use CG\PurchaseOrder\Service as PurchaseOrderService;
use CG\PurchaseOrder\Mapper as PurchaseOrderMapper;
use CG\PurchaseOrder\Filter as PurchaseOrderFilter;
use CG\PurchaseOrder\Collection as PurchaseOrderCollection;
use CG\Product\Client\Service as ProductService;
use CG\Product\Filter as ProductFilter;

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

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        PurchaseOrderService $purchaseOrderService,
        PurchaseOrderMapper $purchaseOrderMapper,
        ProductService $productService
    ) {
        $this->jsonModelFactory = $jsonModelFactory;
        $this->purchaseOrderService = $purchaseOrderService;
        $this->purchaseOrderMapper = $purchaseOrderMapper;
        $this->productService = $productService;
    }

    public function createAction()
    {
        $number = $this->params()->fromPost('number');
        $products = $this->params()->fromPost('products');

        $purchaseOrder = $this->purchaseOrderMapper->fromArray([
            'number' => $number,
            'items' => $products
        ]);
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
        $number = $this->params()->fromPost('number');
        $products = json_decode($this->params()->fromPost('products'));

        try {
            $purchaseOrder = $this->purchaseOrderService->fetch($id);
            $purchaseOrder->setNumber($number);
            $purchaseOrder->setItems($products);

            $this->purchaseOrderService->save($purchaseOrder);
        } catch (NotModified $e) {
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

        return $this->jsonModelFactory->newInstance([
            'success' => true
        ]);
    }

    public function completeAction()
    {
        $id = $this->params()->fromPost('id');
        try {
            $purchaseOrder = $this->purchaseOrderService->fetch($id);
            $purchaseOrder->setStatus(PurchaseOrderEntity::COMPLETE);

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
        $ouId = 1;
        $filter = (new PurchaseOrderFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setOrganisationUnitId([$ouId]);
        $records = $this->purchaseOrderService->fetchCollectionByFilter($filter);

        return $this->jsonModelFactory->newInstance([
            'list' => $this->hydratePurchaseOrdersWithProducts($records),
        ]);
    }

    protected function hydratePurchaseOrdersWithProducts(PurchaseOrderCollection $purchaseOrders)
    {
        $allProductSkus = [];
        foreach ($purchaseOrders as $purchaseOrder) {
            foreach ($purchaseOrder->getItems() as $purchaseOrderItem) {
                $allProductSkus[$purchaseOrder->getId()] = $purchaseOrderItem->getSku();
            }
        }

        $filter = (new ProductFilter())
            ->setLimit('all')
            ->setPage(1)
            ->setSku(array_values($allProductSkus));
        $products = $this->productService->fetchCollectionByFilter($filter);

        $purchaseOrderWithProducts = [];
        foreach ($purchaseOrders as $purchaseOrder) {
            $purchaseOrderWithProduct = $purchaseOrder->toArray();
            foreach ($purchaseOrderWithProduct['items'] as &$item) {
                $item['product'] = $products->getBy('sku', $item['sku']);
            }
            $purchaseOrderWithProducts[] = $purchaseOrderWithProduct;
        }
        return $purchaseOrderWithProducts;
    }
}
