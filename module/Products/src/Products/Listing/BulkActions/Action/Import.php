<?php
namespace Products\Listing\BulkActions\Action;

use CG_UI\View\BulkActions\Action;
use Zend\View\Model\ViewModel;
use SplObjectStorage;

class Import extends Action
{
    const ICON = 'sprite-import-20-black';
    const TYPE = 'import';

    public function __construct(
        array $elementData = [],
        ViewModel $javascript = null,
        SplObjectStorage $subActions = null
    ) {
        parent::__construct(static::ICON, ucwords(static::TYPE), static::TYPE, $elementData, $javascript, $subActions);
    }
}