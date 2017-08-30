<?php
namespace Products\Controller;

use CG\Http\Exception\Exception3xx\NotModified;
use CG\PurchaseOrder\Mapper as PurchaseOrderMapper;
use CG\PurchaseOrder\Service as PurchaseOrderService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\ActiveUserInterface;
use CG\Zend\Stdlib\Http\FileResponse;
use CG_UI\View\Prototyper\JsonModelFactory;
use Zend\Mvc\Controller\AbstractActionController;

class PurchaseOrdersJsonController extends AbstractActionController implements LoggerAwareInterface
{
    use LogTrait;

    const ROUTE_LIST = 'AJAX List';
    const ROUTE_COMPLETE = 'AJAX Complete';
    const ROUTE_DOWNLOAD = 'AJAX Download';
    const ROUTE_DELETE = 'AJAX Delete';
    const ROUTE_SAVE = 'AJAX Save';
    const ROUTE_CREATE = 'AJAX Create';
    const DEFAULT_PO_STATUS = 'In Progress';

    protected $jsonModelFactory;
    protected $purchaseOrderService;
    protected $purchaseOrderMapper;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        PurchaseOrderService $purchaseOrderService,
        PurchaseOrderMapper $purchaseOrderMapper,
        ActiveUserInterface $activeUserContainer
    ) {
        $this->jsonModelFactory = $jsonModelFactory;
        $this->purchaseOrderService = $purchaseOrderService;
        $this->purchaseOrderMapper = $purchaseOrderMapper;
        $this->activeUserContainer = $activeUserContainer;
    }

    public function createAction()
    {
        $externalId = $this->params()->fromPost('externalId');
        $purchaseOrderItems = json_decode($this->params()->fromPost('purchaseOrderItems'), true);

        $purchaseOrder = $this->purchaseOrderMapper->fromArray([
            'externalId' => $externalId,
            'organisationUnitId' => $this->activeUserContainer->getActiveUserRootOrganisationUnitId(),
            'status' => static::DEFAULT_PO_STATUS,
            'created' => date('Y-m-d H:i:s'),
        ]);
        $purchaseOrder = $this->purchaseOrderService->save($purchaseOrder, $purchaseOrderItems);

        return $this->jsonModelFactory->newInstance([
            'success' => true,
            'id' => $purchaseOrder->getId(),
        ]);
    }

    public function saveAction()
    {
        $id = $this->params()->fromPost('id');
        $externalId = $this->params()->fromPost('externalId');
        $updatedPurchaseOrderItems = json_decode($this->params()->fromPost('purchaseOrderItems'), true);
        $purchaseOrder = null;

        try {
            $purchaseOrder = $this->purchaseOrderService->fetch($id);
            $purchaseOrder->setExternalId($externalId);
            $this->purchaseOrderService->save($purchaseOrder, $updatedPurchaseOrderItems);
        } catch (\Exception $e) {
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
            $fileName = date('Y-m-d hi') . " purchase_order.csv";

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
            $this->purchaseOrderService->markAsComplete($id);
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
        try {
            $ouId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
            $records = $this->purchaseOrderService->fetchAllForOu($ouId);
        } catch (NotFound $e) {
            return $this->jsonModelFactory->newInstance(['list' => []]);
        }

        return $this->jsonModelFactory->newInstance([
            'list' => $this->purchaseOrderMapper->hydratePurchaseOrdersWithProducts($records, $ouId),
        ]);
    }
}
