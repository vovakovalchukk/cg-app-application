<?php
namespace Orders\Order\Csv\Mapper\Formatter;

use Orders\Order\Csv\Mapper\FormatterInterface;
use CG\Order\Shared\Entity as Order;
use CG\Stdlib\DateTime;

class InvoiceDate implements FormatterInterface
{
    const FORMAT_DATE = 'd/m/Y';

    public function __invoke(Order $order, $fieldName)
    {
        $invoiceDate = $this->getInvoiceDate($order);

        if($order->getItems()->count() === 0) {
            return [$invoiceDate];
        }

        $column = [];
        for($i = 0; $i < $order->getItems()->count(); $i++) {
            $column[] = $invoiceDate;
        }
        return $column;
    }

    protected function getInvoiceDate(Order $order)
    {
        $purchaseDate = new DateTime($order->getPurchaseDate());
        return $purchaseDate->format(static::FORMAT_DATE);
    }
}
