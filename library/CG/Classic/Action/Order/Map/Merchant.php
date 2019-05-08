<?php
namespace CG\Classic\Action\Order\Map;

use CG\Channel\Action\Order\Map as DefaultMap;
use CG\Order\Shared\Status;

class Merchant extends DefaultMap
{
    public static function getActionToStatusMap()
    {
        // Actions to be implemented in MIG-5
        return [
            Status::INCOMPLETE => [],
            Status::AWAITING_PAYMENT => [],
            Status::NEW_ORDER => [],
            Status::DISPATCHING => [],
            Status::DISPATCHED => [],
            Status::DISPATCH_FAILED => [],
            Status::UNKNOWN => [],
            Status::CANCELLING => [],
            Status::CANCELLED => [],
            Status::CANCEL_FAILED => [],
            Status::REFUNDING => [],
            Status::REFUNDED => [],
            Status::REFUND_FAILED => [],
            Status::BUYER_DISPUTE => []
        ];
    }
}