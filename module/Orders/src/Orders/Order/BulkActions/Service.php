<?php
namespace Orders\Order\BulkActions;

use CG\Stdlib\Exception\Runtime\NotFound;
use CG_UI\View\BulkActions;
use CG_UI\View\BulkActions\SubAction;
use CG_UI\View\BulkActions\Action;
use CG\Order\Shared\Entity as OrderEntity;
use Orders\Courier\Service as CourierService;
use Orders\Order\BulkActions\Action\Courier as CourierBulkAction;

class Service
{
    protected $bulkActions;
    protected $orderBulkActions;
    /** @var CourierService */
    protected $courierService;

    public function __construct(
        BulkActions $bulkActions,
        BulkActions $orderBulkActions,
        CourierService $courierService
    )
    {
        $this->setBulkActions($bulkActions)->setOrderBulkActions($orderBulkActions);
        $this->courierService = $courierService;
    }

    /**
     * @return Service
     */
    public function setBulkActions(BulkActions $bulkActions)
    {
        $this->bulkActions = $bulkActions;
        return $this;
    }

    /**
     * @return BulkActions
     */
    public function getBulkActions()
    {
        return $this->bulkActions;
    }

    /**
     * @return Service
     */
    public function setOrderBulkActions(BulkActions $orderBulkActions)
    {
        $this->orderBulkActions = $orderBulkActions;
        return $this;
    }

    /**
     * @return BulkActions
     */
    public function getOrderBulkActions(OrderEntity $orderEntity)
    {
        foreach ($this->orderBulkActions->getActions() as $action) {
            $this->appendOrderToAction($action, $orderEntity);
        }

        return $this->orderBulkActions;
    }

    public function getBulkActionsForOrder(OrderEntity $order)
    {
        $bulkActions = $this->getOrderBulkActions($order);
        try {
            $courierAccounts = $this->courierService->getShippingAccounts();
            foreach ($bulkActions->getActions() as $action) {
                if (!($action instanceof CourierBulkAction)) {
                    continue;
                }
                $bulkActions->getActions()->detach($action);
            }
            return $bulkActions;
        } catch (NotFound $e) {
            return $bulkActions;
        }
    }

    protected function appendOrderToAction(SubAction $action, OrderEntity $orderEntity)
    {
        if ($action instanceof OrderAwareInterface) {
            $action->setOrder($orderEntity);
        }

        if ($action->hasJavascript()) {
            $action->getJavascript()->setVariable('order', $orderEntity);
        }

        if (!($action instanceof Action) || !$action->hasSubActions()) {
            return;
        }

        foreach ($action->getSubActions() as $subAction) {
            $this->appendOrderToAction($subAction, $orderEntity);
        }
    }
}