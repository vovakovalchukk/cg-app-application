<?php
namespace Orders\Order\Csv\Mapper;

use CG\Order\Shared\Entity as Order;

interface FormatterInterface
{
    public function __invoke(Order $order, $fieldName);
}
