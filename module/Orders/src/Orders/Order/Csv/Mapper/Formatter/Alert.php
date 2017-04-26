<?php
namespace Orders\Order\Csv\Mapper\Formatter;

use CG\Order\Shared\Entity as Order;
use Orders\Order\Csv\Mapper\FormatterInterface;

class Alert implements FormatterInterface
{
    public function __invoke(Order $order, $fieldName)
    {
        $column = [];

        $alerts = [];
        foreach ($order->getAlerts() as $alert) {
            $alerts[] = $alert->getAlert();
        }
        $column[] = implode('; ', $alerts);
        for($i = 1; $i < $order->getItems()->count(); $i++) {
            $column[] = '';
        }

        return $column;
    }
}
