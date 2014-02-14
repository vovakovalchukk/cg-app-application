<?php
namespace Orders\Order\TableService;

use CG_UI\View\DataTable;

interface OrdersTableModifierInterface
{
    public function modifyTable(DataTable $ordersTable);
}