<?php
namespace CG\ManualOrder\Action\Order\Map;

use CG\Channel\Action\Order\Map as DefaultMap;
use CG\Order\Shared\Status;

class Merchant extends DefaultMap
{
    public static function getActionToStatusMap()
    {
        return [
            Status::AWAITING_PAYMENT => [
                static::CANCEL,
                static::PAY
            ],
            Status::NEW_ORDER => [
                static::CANCEL,
                static::DISPATCH,
                static::REFUND
            ],
            Status::DISPATCHING => [
                static::CANCEL,
                static::REFUND
            ],
            Status::DISPATCHED => [
                static::REFUND
            ],
            Status::UNKNOWN => [
                static::CANCEL,
                static::DISPATCH,
                static::REFUND
            ],
            Status::CANCELLING => [],
            Status::CANCELLED => [],
            Status::CANCEL_FAILED => [
                static::CANCEL,
                static::REFUND,
                static::DISPATCH
            ],
            Status::REFUNDING => [],
            Status::REFUNDED => [],
            Status::REFUND_FAILED => [
                static::CANCEL,
                static::REFUND,
                static::DISPATCH
            ],
            Status::BUYER_DISPUTE => []
        ];
    }
} 
