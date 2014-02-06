<?php
namespace Orders\Order\BulkActions\SubAction;

use CG_UI\View\BulkActions\SubAction;
use Zend\View\Model\ViewModel;

class Tag extends SubAction
{
    public function __construct(array $elementData = [], ViewModel $javascript = null)
    {
        parent::__construct('Tag', 'tag', $elementData, $javascript);
    }
}