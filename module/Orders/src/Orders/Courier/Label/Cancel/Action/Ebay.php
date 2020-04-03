<?php
namespace Orders\Courier\Label\Cancel\Action;

use CG\Order\Shared\Entity as Order;
use Orders\Courier\Label\Cancel\CancelActionInterface;

class Ebay implements CancelActionInterface
{
    public function postTrackingNumberRemovalAction(Order $order): void
    {
        // TODO: Implement postTrackingNumberRemovalAction() method.
    }
}