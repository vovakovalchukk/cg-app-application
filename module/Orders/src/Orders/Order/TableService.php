<?php
namespace Orders\Order;

use CG_UI\View\DataTable;
use SplObjectStorage;

class TableService
{
    protected $ordersTable;
    protected $ordersTableModifiers;
    protected $ordersTableConfigured = false;

    public function __construct(DataTable $ordersTable)
    {
        $this->setOrdersTable($ordersTable);
        $this->ordersTableModifiers = new SplObjectStorage();
    }

    public function setOrdersTable(DataTable $ordersTable)
    {
        if ($this->ordersTable === $ordersTable) {
            return $this;
        }
        $this->ordersTable = $ordersTable;
        $this->ordersTableConfigured = false;
        return $this;
    }

    public function addOrderTableModifier(TableService\OrdersTableModifierInterface $orderTableModifier)
    {
        $this->ordersTableModifiers->attach($orderTableModifier);
    }

    protected function configureOrdersTable()
    {
        $this->ordersTableConfigured = true;
        foreach ($this->ordersTableModifiers as $orderTableModifier) {
            $orderTableModifier->modifyTable($this->getOrdersTable());
        }
    }

    /**
     * @return DataTable
     */
    public function getOrdersTable()
    {
        if (!$this->ordersTableConfigured) {
            $this->configureOrdersTable();
        }
        return $this->ordersTable;
    }
}