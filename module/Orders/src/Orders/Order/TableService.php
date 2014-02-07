<?php
namespace Orders\Order;

use CG_UI\View\DataTable;
use CG\Order\Shared\Tag\StorageInterface as TagStorage;

class TableService
{
    protected $tagClient;
    protected $ordersTable;

    public function __construct(DataTable $ordersTable, TagStorage $tagClient)
    {
        $this->setTagClient($tagClient)->setOrdersTable($ordersTable);
    }

    public function setTagClient(TagStorage $tagClient)
    {
        $this->tagClient = $tagClient;
        return $this;
    }

    /**
     * @return TagStorage
     */
    public function getTagClient()
    {
        return $this->tagClient;
    }

    public function setOrdersTable(DataTable $ordersTable)
    {
        $this->ordersTable = $ordersTable;
        return $this;
    }

    /**
     * DataTable
     */
    public function getOrdersTable()
    {
        return $this->ordersTable;
    }
}