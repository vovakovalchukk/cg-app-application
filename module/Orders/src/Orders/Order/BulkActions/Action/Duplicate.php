<?php
namespace Orders\Order\BulkActions\Action;

use CG\Order\Shared\Entity as Order;
use CG_UI\View\BulkActions\Action;
use Orders\Order\BulkActions\OrderAwareInterface;
use SplObjectStorage;
use Zend\View\Model\ViewModel;

class Duplicate extends Action implements OrderAwareInterface
{
    const ICON = 'sprite-batch-22-black';

    public function __construct(
        array $elementData = [],
        ViewModel $javascript = null,
        SplObjectStorage $subActions = null
    ) {
        parent::__construct(self::ICON, 'Duplicate', 'duplicate', $elementData, $javascript, $subActions);
    }

    public function setOrder(Order $order)
    {
        $this->setEnabled(true);
    }
}
