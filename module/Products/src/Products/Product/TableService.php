<?php
namespace Products\Product;

use CG_UI\View\DataTable;
use SplObjectStorage;

class TableService
{
    protected $productsTable;
    protected $productsTableModifiers;
    protected $productsTableConfigured = false;

    public function __construct(DataTable $productsTable)
    {
        $this->setProductsTable($productsTable);
        $this->productsTableModifiers = new SplObjectStorage();
    }

    public function setProductsTable(DataTable $productsTable)
    {
        if ($this->productsTable === $productsTable) {
            return $this;
        }
        $this->productsTable = $productsTable;
        $this->productsTableConfigured = false;
        return $this;
    }

    public function addProductTableModifier(TableService\ProductsTableModifierInterface $orderTableModifier)
    {
        $this->productsTableModifiers->attach($orderTableModifier);
    }

    protected function configureProductsTable()
    {
        $this->productsTableConfigured = true;
        foreach ($this->productsTableModifiers as $orderTableModifier) {
            $orderTableModifier->modifyTable($this->getProductsTable());
        }
    }

    /**
     * @return DataTable
     */
    public function getProductsTable()
    {
        if (!$this->productsTableConfigured) {
            $this->configureProductsTable();
        }
        return $this->productsTable;
    }
}