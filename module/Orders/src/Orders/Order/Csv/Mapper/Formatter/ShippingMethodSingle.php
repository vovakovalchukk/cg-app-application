<?php
namespace Orders\Order\Csv\Mapper\Formatter;

use CG\Order\Shared\Entity as Order;

class ShippingMethodSingle extends ShippingMethod
{
    public function __invoke(Order $order, $fieldName)
    {
        return $this->getShippingMethod($order);
    }
} 
