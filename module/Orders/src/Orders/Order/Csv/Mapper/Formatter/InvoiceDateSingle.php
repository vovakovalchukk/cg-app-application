<?php
namespace Orders\Order\Csv\Mapper\Formatter;

use CG\Order\Shared\Entity as Order;

class InvoiceDateSingle extends InvoiceDate
{
    public function __invoke(Order $order, $fieldName)
    {
        return $this->getInvoiceDate($order);
    }
}


