<?php
namespace Orders\Order\BulkActions\Action;

use CG\Order\Shared\Cancel\Value as CancelValue;
use CG\Channel\Action\Order\MapInterface as ActionDeciderMap;

class Refund extends Cancel
{
    const TYPE = CancelValue::REFUND_TYPE;
    const ALLOWED_ACTION = ActionDeciderMap::REFUND;
} 