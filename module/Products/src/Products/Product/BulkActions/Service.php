<?php
namespace Products\Product\BulkActions;

use CG_UI\View\BulkActions;
use CG_UI\View\BulkActions\SubAction;
use CG_UI\View\BulkActions\Action;
use CG\Product\Entity as ProductEntity;
use Products\Product\BulkActions\ProductAwareInterface;

class Service
{
    protected $listPageBulkActions;
    protected $detailPageBulkActions;

    public function __construct(BulkActions $listPageBulkActions, BulkActions $detailPageBulkActions)
    {
        $this->setListPageBulkActions($listPageBulkActions)->setDetailPageBulkActions($detailPageBulkActions);
    }

    /**
     * @return Service
     */
    public function setListPageBulkActions(BulkActions $listPageBulkActions)
    {
        $this->listPageBulkActions = $listPageBulkActions;
        return $this;
    }

    /**
     * @return BulkActions
     */
    public function getListPageBulkActions()
    {
        return $this->listPageBulkActions;
    }

    /**
     * @return Service
     */
    public function setDetailPageBulkActions(BulkActions $detailPageBulkActions)
    {
        $this->detailPageBulkActions = $detailPageBulkActions;
        return $this;
    }

    /**
     * @return BulkActions
     */
    public function getDetailPageBulkActions(ProductEntity $productEntity)
    {
        foreach ($this->detailPageBulkActions->getActions() as $action) {
            $this->appendProductToAction($action, $productEntity);
        }

        return $this->detailPageBulkActions;
    }

    protected function appendProductToAction(SubAction $action, ProductEntity $productEntity)
    {
        if ($action instanceof ProductAwareInterface) {
            $action->setProduct($productEntity);
        }

        if ($action->hasJavascript()) {
            $action->getJavascript()->setVariable('product', $productEntity);
        }

        if (!($action instanceof Action) || !$action->hasSubActions()) {
            return;
        }

        foreach ($action->getSubActions() as $subAction) {
            $this->appendProductToAction($subAction, $productEntity);
        }
    }
}