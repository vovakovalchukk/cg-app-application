<?php
namespace Filters\Options;

use CG_UI\View\Filters\SelectOptionsInterface;
use CG\Order\Shared\Status;

class OrderStatus implements SelectOptionsInterface
{
    public function getSelectOptions()
    {
        $statuses = Status::getAllStatuses();
        $statusOptions = [];
        foreach ($statuses as $status) {
            $uiStatus = ucwords(str_replace(['-', '_'], ' ', $status));
            $statusOptions[$status] = $uiStatus;
        }
        return $statusOptions;
    }
}
