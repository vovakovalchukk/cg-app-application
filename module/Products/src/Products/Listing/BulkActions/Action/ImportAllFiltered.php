<?php
namespace Products\Listing\BulkActions\Action;

use CG_UI\View\BulkActions\SubAction;
use Zend\View\Model\ViewModel;

class ImportAllFiltered extends SubAction
{
    public function __construct(ViewModel $javascript)
    {
        parent::__construct('Import all filtered', '', [], $javascript);
    }
}