<?php
namespace Orders\Courier\Label\Cancel;

use CG\Order\Shared\Entity as Order;

interface CancelActionInterface
{
    public function postTrackingNumberRemovalAction(Order $order): void;
}