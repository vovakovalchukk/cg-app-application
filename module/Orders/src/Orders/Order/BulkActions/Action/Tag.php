<?php
namespace Orders\Order\BulkActions\Action;

use CG_UI\View\BulkActions\Action;
use Zend\View\Model\ViewModel;

class Tag extends Action
{
    public function __construct(array $elementData = [], ViewModel $javascript = null) {
        parent::__construct('tag-untag', 'Tag', 'tag', $elementData, $javascript);
    }
} 