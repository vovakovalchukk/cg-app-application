<?php
namespace CG\Classic\Action\Order\Map;

use CG\Channel\Action\Order\Map as DefaultMap;
use CG\Order\Shared\Status;

class Merchant extends DefaultMap
{
    public static function getActionToStatusMap()
    {
        return [
            Status::INCOMPLETE => [],
            Status::AWAITING_PAYMENT => [],
            Status::NEW_ORDER => [static::DISPATCH],
            Status::DISPATCHING => [],
            Status::DISPATCHED => [],
            Status::DISPATCH_FAILED => [static::DISPATCH],
            Status::UNKNOWN => [static::DISPATCH],
            Status::CANCELLING => [],
            Status::CANCELLED => [],
            Status::CANCEL_FAILED => [static::DISPATCH],
            Status::REFUNDING => [],
            Status::REFUNDED => [],
            Status::REFUND_FAILED => [static::DISPATCH],
            Status::BUYER_DISPUTE => []
        ];
    }
}