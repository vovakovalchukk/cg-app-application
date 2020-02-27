<?php
namespace Orders\Controller;

use CG\Order\Client\Action\Service as OrderActionService;
use CG\Order\Client\Service as OrderService;
use CG\Order\Shared\Item\Collection as ItemCollection;
use CG\Order\Shared\Item\Entity as Item;
use CG\Order\Shared\Item\Refund\Collection as ItemRefundCollection;
use CG\Order\Shared\Item\Refund\Entity as ItemRefund;
use CG\Order\Shared\Item\Refund\Service as ItemRefundService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG_UI\View\Prototyper\JsonModelFactory;
use CG\User\ActiveUserInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class PartialRefundController extends AbstractActionController
{
    /** @var JsonModelFactory */
    protected $jsonModelFactory;
    /** @var OrderService */
    protected $orderService;
    /** @var OrderActionService */
    protected $orderActionService;
    /** @var ItemRefundService */
    protected $itemRefundService;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;

    public function __construct(
        JsonModelFactory $jsonModelFactory,
        OrderService $orderService,
        OrderActionService $orderActionService,
        ItemRefundService $itemRefundService,
        ActiveUserInterface $activeUserContainer
    ) {
        $this->jsonModelFactory = $jsonModelFactory;
        $this->orderService = $orderService;
        $this->orderActionService = $orderActionService;
        $this->itemRefundService = $itemRefundService;
        $this->activeUserContainer = $activeUserContainer;
    }

    public function partialRefundAction(): JsonModel
    {
        $orderId = $this->params()->fromPost('orderId');
        $refundReason = $this->params()->fromPost('refundReason');
        $itemData = $this->params()->fromPost('items');
        if (!$orderId || !$refundReason || !$itemData || !is_array($itemData)) {
            throw new \InvalidArgumentException(__METHOD__ . ' requires a valid orderId, refundReason and array of items data');
        }
        $order = $this->orderService->fetch($orderId);
        if (!$this->areAmountsWithinTotalRefundable($itemData, $order->getItems())) {
            return $this->jsonModelFactory->newInstance([
                'success' => false,
                'message' => 'Some of the amounts entered are higher than the refundable amount'
            ]);
        }
        $ouId = $this->activeUserContainer->getActiveUserRootOrganisationUnitId();
        $userId = $this->activeUserContainer->getActiveUser()->getId();
        $success = $this->orderActionService->partiallyRefundOrder($order, $itemData, $refundReason, $ouId, $userId);
        return $this->jsonModelFactory->newInstance([
            'success' => $success,
            'status' => $order->getStatus(),
        ]);
    }

    protected function areAmountsWithinTotalRefundable(array $itemData, ItemCollection $items): bool
    {
        try {
            $allItemRefunds = $this->itemRefundService->fetchCollectionByItemIds(array_column($itemData, 'id'));
        } catch (NotFound $e) {
            $allItemRefunds = new ItemRefundCollection(ItemRefund::class, 'empty');
        }
        foreach ($itemData as $itemDatum) {
            /** @var Item $item */
            $item = $items->getById($itemDatum['id']);
            $itemRefunds = $allItemRefunds->getBy('itemId', $item->getId());
            if ($itemDatum['amount'] > $item->getRefundableAmount($itemRefunds)) {
                return false;
            }
        }
        return true;
    }
}