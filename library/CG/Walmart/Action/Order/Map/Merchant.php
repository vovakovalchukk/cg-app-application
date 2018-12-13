<?php
namespace CG\Walmart\Action\Order\Map;

use CG\Channel\Action\Order\Map as DefaultMap;
use CG\Order\Shared\Status;

class Merchant extends DefaultMap
{
    public static function getActionToStatusMap()
    {
        return [
            Status::AWAITING_PAYMENT => [
                static::CANCEL,
            ],
            Status::NEW_ORDER => [
                static::DISPATCH,
                static::CANCEL,
                static::REFUND,
            ],
            Status::DISPATCHING => [
                static::CANCEL,
                static::REFUND,
            ],
            Status::DISPATCHED => [
                static::REFUND,
            ],
            Status::DISPATCH_FAILED => [
                static::DISPATCH,
                static::CANCEL,
                static::REFUND,
            ],
            Status::UNKNOWN => [
                static::DISPATCH,
                static::CANCEL,
                static::REFUND,
            ],
            Status::CANCELLING => [],
            Status::CANCELLED => [],
            Status::CANCEL_FAILED => [
                static::DISPATCH,
                static::CANCEL,
            ],
            Status::REFUNDING => [],
            Status::REFUNDED => [],
            Status::REFUND_FAILED => [
                static::DISPATCH,
                static::CANCEL,
                static::REFUND,
            ],
            Status::BUYER_DISPUTE => [],
        ];
    }
}
