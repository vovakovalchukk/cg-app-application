<?php
namespace Orders\Order\BulkActions;

use CG_UI\View\BulkActions;
use CG_UI\View\BulkActions\SubAction;
use CG_UI\View\BulkActions\Action;
use CG\Order\Shared\Entity as OrderEntity;

class Service
{
    protected $bulkActions;
    protected $orderBulkActions;

    public function __construct(BulkActions $bulkActions, BulkActions $orderBulkActions)
    {
        $this->setBulkActions($bulkActions)->setOrderBulkActions($orderBulkActions);
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