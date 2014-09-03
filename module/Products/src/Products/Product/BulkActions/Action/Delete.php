<?php
namespace Products\Product\BulkActions\Action;

use CG_UI\View\BulkActions\Action;
use Zend\View\Model\ViewModel;
use SplObjectStorage;

class Delete extends Action
{
    const ICON = 'sprite-cancel-22-black';
    const TYPE = 'delete';

    public function __construct(
        array $elementData = [],
        ViewModel $javascript = null,
        SplObjectStorage $subActions = null
    ) {
        parent::__construct(static::ICON, ucwords(static::TYPE), static::TYPE, $elementData, $javascript, $subActions);
    }
}
