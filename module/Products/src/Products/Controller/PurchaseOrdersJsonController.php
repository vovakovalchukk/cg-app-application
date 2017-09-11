<?php
namespace Products\Controller;

use Application\Controller\AbstractJsonController;
use CG\Http\Exception\Exception3xx\NotModified;
use CG\PurchaseOrder\Collection as PurchaseOrderCollection;
use CG\PurchaseOrder\Entity as PurchaseOrder;
use CG\PurchaseOrder\Item\Service as ItemService;
use CG\PurchaseOrder\Item\Entity as ItemEntity;
use CG\PurchaseOrder\Mapper as PurchaseOrderMapper;
use CG\PurchaseOrder\Service as PurchaseOrderService;
use CG\PurchaseOrder\Status as PurchaseOrderStatus;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LogTrait;
use CG\User\ActiveUserInterface;
use CG\Zend\Stdlib\Http\FileResponse;
use CG_UI\View\Prototyper\JsonModelFactory;

class PurchaseOrdersJsonController extends AbstractJsonController
{
    use LogTrait;

    const ROUTE_LIST = 'AJAX List';
    const ROUTE_COMPLETE = 'AJAX Complete';
    const ROUTE_DOWNLOAD = 'AJAX Download';
    const ROUTE_DELETE = 'AJAX Delete';
    const ROUTE_SAVE = 'AJAX Save';
    const ROUTE_CREATE = 'AJAX Create';
    const DEFAULT_PO_STATUS = 'In Progress';

    /** @var PurchaseOrderService */
    protected $purchaseOrderService;
    /** @var PurchaseOrderMapper */
    protected $purchaseOrderMapper;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var ItemService */
    protected $itemService;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        PurchaseOrderService $purchaseOrderService,
        ItemService $itemService,
        PurchaseOrderMapper $purchaseOrderMapper,
        ActiveUserInterface $activeUserContainer
    ) {
        parent::__construct($jsonModelFactory);
        $this->purchaseOrderService = $purchaseOrderService;
        $this->itemService = $itemService;
        $this->purchaseOrderMapper = $purchaseOrderMapper;
        $this->activeUserContainer = $activeUserContainer;
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
            /** @var ItemEntity $item */
            foreach ($purchaseOrder->getItems() as $item) {
                $this->itemService->remove($item);
            }
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
            $purchaseOrder = $this->purchaseOrderService->fetch($id);
            $purchaseOrderCsv = $this->purchaseOrderService->convertToCsv($purchaseOrder);
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
}
