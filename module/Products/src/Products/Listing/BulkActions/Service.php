<?php
namespace Products\Listing\BulkActions;

use CG_UI\View\ProductBulkActions as BulkActions;
use CG_UI\View\Prototyper\ViewModelFactory;

class Service
{
    protected $listPageBulkActions;
    protected $viewModelFactory;

    public function __construct(
        BulkActions $listPageBulkActions,
        ViewModelFactory $viewModelFactory
    ) {
        $this->setViewModelFactory($viewModelFactory)
            ->setListPageBulkActions($listPageBulkActions);
    }

    protected function setListPageBulkActions(BulkActions $listPageBulkActions)
    {
        $this->listPageBulkActions = $listPageBulkActions;
        return $this;
    }

    public function getListPageBulkActions()
    {
        return $this->listPageBulkActions;
    }

    protected function setViewModelFactory(ViewModelFactory $viewModelFactory)
    {
        $this->viewModelFactory = $viewModelFactory;
        return $this;
    }

    protected function getViewModelFactory()
    {
        return $this->viewModelFactory;
    }
}