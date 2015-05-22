<?php
namespace Orders\Order\Table\Row;

use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Item\Entity as Item;
use CG_UI\View\Table\Column\Collection as Columns;
use CG_UI\View\Table\Row\Mapper as UIMapper;
use Zend\I18n\View\Helper\CurrencyFormat;

class Mapper extends UIMapper
{
    const COLUMN_SKU = 'SKU';
    const COLUMN_PRODUCT = 'Product Name';
    const COLUMN_QUANTITY = 'Quantity';
    const COLUMN_PRICE = 'Price inc. VAT';
    const COLUMN_DISCOUNT = 'Discount Total';
    const COLUMN_TOTAL = 'Line Total';

    protected $currencyFormat;
    protected $order;

    protected $mapItem = [
        self::COLUMN_SKU => ['getter' => 'getItemSku', 'callback' => null],
        self::COLUMN_PRODUCT => ['getter' => 'getItemName', 'callback' => 'formatItemLink'],
        self::COLUMN_QUANTITY => ['getter' => 'getItemQuantity', 'callback' => null],
        self::COLUMN_PRICE => ['getter' => 'getItemPrice', 'callback' => 'formatItemCurrency'],
        self::COLUMN_DISCOUNT => ['getter' => 'getItemDiscountTotal', 'callback' => 'formatItemCurrency'],
        self::COLUMN_TOTAL => ['getter' => 'getItemLineTotal', 'callback' => 'formatItemCurrency'],
    ];
    protected $mapDiscount = [
        self::COLUMN_SKU => ['getter' => 'getItemSku', 'callback' => null],
        self::COLUMN_PRODUCT => ['getter' => 'getItemName', 'callback' => 'formatItemLink'],
        self::COLUMN_QUANTITY => ['getter' => 'getItemQuantity', 'callback' => null],
        self::COLUMN_PRICE => ['getter' => 'getItemPrice', 'callback' => 'formatItemCurrency'],
        self::COLUMN_DISCOUNT => ['getter' => 'getItemDiscountTotal', 'callback' => 'formatItemCurrency'],
        self::COLUMN_TOTAL => ['getter' => 'getItemLineTotal', 'callback' => 'formatItemCurrency'],
    ];

    public function __construct(CurrencyFormat $currencyFormat)
    {
        $this->setCurrencyFormat($currencyFormat);
    }

    public function fromItem(Item $item, Order $order, Columns $columns, $className = null)
    {
        $this->setOrder($order);
        $rowData = [];
        foreach ($columns as $column) {
            $columnName = $column->getName();
            if (!isset($this->mapItem[$columnName])) {
                $rowData[] = '';
                continue;
            }
            $map = $this->mapItem[$columnName];
            $value = $this->getCellValue($item, $map);
            $rowData[] = [
                'content' => $this->formatCellValue($value, $item, $map),
                'class' => $column->getClass()
            ]; 
        }
        return $this->fromArray($rowData, $className);
    }

    protected function getCellValue($entity, array $map)
    {
        if (is_callable([$this, $map['getter']])) {
            return $this->{$map['getter']}($entity);
        }
        return $entity->{$map['getter']}();
    }

    protected function formatCellValue($value, $entity, array $map)
    {
        if (!$map['callback'] || !is_callable([$this, $map['callback']])) {
            return $value;
        }
        return $this->{$map['callback']}($entity, $value);
    }

    protected function getItemPrice(Item $item)
    {
        return $item->getIndividualItemPrice() + $item->getIndividualItemDiscountPrice();
    }

    protected function getItemDiscountTotal(Item $item)
    {
        return '-' . $item->getIndividualItemDiscountPrice() * $item->getItemQuantity();
    }

    protected function getItemLineTotal(Item $item)
    {
        return $item->getIndividualItemPrice() * $item->getItemQuantity();
    }

    protected function formatItemLink(Item $entity, $value)
    {
        if (empty($entity->getUrl())) {
            return $value;
        }
        return '<a href="' . $entity->getUrl() . '" target="_blank">' . $value . '</a>';
    }

    protected function formatItemCurrency(Item $entity, $value)
    {
        $currencyCode = $this->order->getCurrencyCode();
        $formatter = $this->currencyFormat;
        return $formatter($value, $currencyCode);
    }

    protected function setCurrencyFormat(CurrencyFormat $currencyFormat)
    {
        $this->currencyFormat = $currencyFormat;
        return $this;
    }

    public function setOrder(Order $order)
    {
        $this->order = $order;
        return $this;
    }
}
