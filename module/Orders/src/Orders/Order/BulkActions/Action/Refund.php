<?php
namespace Orders\Order\BulkActions\Action;

use CG\Order\Shared\Cancel\Value as CancelValue;
use CG\Channel\Action\Order\MapInterface as ActionDeciderMap;
use CG\Order\Shared\Cancel\Reasons;

class Refund extends Cancel
{
    const ICON = 'sprite-accounting-22-black';
    const TYPE = CancelValue::REFUND_TYPE;
    const ALLOWED_ACTION = ActionDeciderMap::REFUND;

    protected function getReasons()
    {
        return json_encode(Reasons::getAllRefundReasons());
    }
}
