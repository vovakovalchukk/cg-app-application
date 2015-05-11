<?php
namespace Orders\Order\Csv\Mapper\Formatter;

use CG\Order\Shared\Entity as Order;

class SalesChannelNameSingle extends SalesChannelName
{
    public function __invoke(Order $order, $fieldName)
    {
        return $this->fetchAccountDisplayName($order->getAccountId());
    }
}
