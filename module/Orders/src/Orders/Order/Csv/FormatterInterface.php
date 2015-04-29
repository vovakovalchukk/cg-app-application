<?php
namespace Orders\Order\Csv;

use CG\Order\Shared\Entity as Order;

interface FormatterInterface
{
    public function __invoke(Order $order);
}
