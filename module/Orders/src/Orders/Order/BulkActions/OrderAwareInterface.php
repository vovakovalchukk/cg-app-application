<?php
namespace Orders\Order\BulkActions;

use CG\Order\Shared\Entity;

interface OrderAwareInterface
{
    public function setOrder(Entity $order);
} 