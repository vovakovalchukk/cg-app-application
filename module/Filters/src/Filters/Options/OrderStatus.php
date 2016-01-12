<?php
namespace Filters\Options;

use CG_UI\View\Filters\SelectOptionsInterface;
use CG\Order\Shared\Status;

class OrderStatus implements SelectOptionsInterface
{
    public function getSelectOptions()
    {
        return Status::getAllStatuses();
    }
}
