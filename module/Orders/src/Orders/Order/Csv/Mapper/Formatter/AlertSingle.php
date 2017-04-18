<?php
namespace Orders\Order\Csv\Mapper\Formatter;

use CG\Order\Shared\Entity as Order;
use Orders\Order\Csv\Mapper\FormatterInterface;

class AlertSingle implements FormatterInterface
{
    public function __invoke(Order $order, $fieldName)
    {
        $alerts = [];
        foreach ($order->getAlerts() as $alert) {
            $alerts[] = $alert->getAlert();
        }
        return implode('; ', $alerts);
    }
}
