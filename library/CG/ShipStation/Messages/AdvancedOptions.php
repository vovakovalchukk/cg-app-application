<?php
namespace CG\ShipStation\Messages;

//advanced_options

use CG\Order\Shared\Courier\Label\OrderItemsData;
use CG\Order\Shared\ShippableInterface as Order;

class AdvancedOptions
{
    public function __construct(
    ) {

    }

    public static function createFromOrder(Order $order, OrderItemsData $itemsData): AdvancedOptions
    {
        return new self();
    }

    public function toArray(): array
    {
        $array = [

        ];
        return $array;
    }
}