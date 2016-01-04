<?php
namespace Products\Product\BulkActions;

use CG_UI\View\BulkActions;
use CG_UI\View\BulkActions\SubAction;
use CG_UI\View\BulkActions\Action;
use CG\Product\Entity as ProductEntity;
use CG_UI\View\Prototyper\ViewModelFactory;

class Service
{
    protected $listPageBulkActions;
    protected $viewModelFactory;

    public function __construct(
        BulkActions $listPageBulkActions,
        ViewModelFactory $viewModelFactory
    ) {
        $this->setViewModelFactory($viewModelFactory);

        $searchView = $this->getViewModelFactory()->newInstance(
            [
                'name' => 'searchTerm',
                'class' => 'product-search-text',
                'placeholder' => 'Search',
            ]
        );
        $searchView->setTemplate('elements/search.mustache');

        $searchButton = $this->getViewModelFactory()->newInstance(
            [
                'type' => 'Submit',
                'class' => 'product-search-button',
                'value' => 'Search',
                'id' => 'searchSubmit',
                'buttons' => 'search'
            ]
        );
        $searchButton->setTemplate('elements/buttons.mustache');

        $listPageBulkActions->addChild($searchView, 'searchUI');
        $listPageBulkActions->addChild($searchButton, 'searchUIButton');
        $this->setListPageBulkActions($listPageBulkActions);
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

    protected function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }

    /**
     * @return ViewModelFactory
     */
    protected function getViewModelFactory()
    {
        return $this->viewModelFactory;
    }
}