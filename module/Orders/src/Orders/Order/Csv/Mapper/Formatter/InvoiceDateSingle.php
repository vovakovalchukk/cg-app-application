<?php
namespace Orders\Order\Csv\Mapper\Formatter;

use CG\Order\Shared\Entity as Order;
use DateTime;

class InvoiceDateSingle extends DateSingle
{
    public function __invoke(Order $order, $fieldName)
    {
        $dateFormatter = $this->dateFormatHelper;
        $date = $order->getInvoiceDate();
        $dateTime = DateTime::createFromFormat(Order::INVOICE_DATE_FORMAT, $date);
        return $dateFormatter($dateTime, Order::INVOICE_DATE_FORMAT);
    }
}
