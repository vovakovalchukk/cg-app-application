<?php
namespace Orders\Order;

use CG_UI\View\DataTable;

class TableService
{
    protected $ordersTable;

    public function __construct(DataTable $ordersTable)
    {
        $this->setOrdersTable($ordersTable);
    }

    public function setOrdersTable(DataTable $ordersTable)
    {
        if ($this->ordersTable === $ordersTable) {
            return $this;
        }
        $this->ordersTable = $ordersTable;
        $this->configureOrdersTable();
        return $this;
    }

    protected function configureOrdersTable()
    {

    }

    /**
     * @return DataTable
     */
    public function getOrdersTable()
    {
        return $this->ordersTable;
    }
}