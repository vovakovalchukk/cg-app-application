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
        self::COLUMN_PRICE => ['getter' => 'getItemPrice', 'callback' => 'formatCurrency'],
        self::COLUMN_DISCOUNT => ['getter' => 'getItemDiscountTotal', 'callback' => 'formatCurrency'],
        self::COLUMN_TOTAL => ['getter' => 'getItemLineTotal', 'callback' => 'formatCurrency'],
    ];
    protected $mapDiscount = [
        self::COLUMN_SKU => ['getter' => 'getOrderDiscountSummary', 'callback' => null, 'colSpan' => 3],
        self::COLUMN_PRICE => ['getter' => 'getOrderDiscountSubHeading', 'callback' => null, 'colSpan' => 2, 'class' => ''],
        self::COLUMN_TOTAL => ['getter' => 'getOrderDiscountTotal', 'callback' => 'formatCurrency'],
    ];

    public function __construct(CurrencyFormat $currencyFormat)
    {
        $this->setCurrencyFormat($currencyFormat);
    }

    public function fromItem(Item $item, Order $order, Columns $columns, $className = null)
    {
        $this->setOrder($order);
        $map = $this->mapItem;
        return $this->fromEntity($item, $map, $columns, $className);
    }

    public function fromOrderDiscount(Order $order, Columns $columns, $className = null)
    {
        $this->setOrder($order);
        $map = $this->mapDiscount;
        return $this->fromEntity($order, $map, $columns, $className);
    }

    protected function fromEntity($entity, $map, Columns $columns, $className = null)
    {
        $rowData = [];
        foreach ($columns as $column) {
            $columnName = $column->getName();
            if (!isset($map[$columnName])) {
                continue;
            }
            $columnMap = $map[$columnName];
            $value = $this->getCellValue($entity, $columnMap);
            $rowData[] = [
                'content' => $this->formatCellValue($value, $entity, $columnMap),
                'class' => (isset($columnMap['class']) ? $columnMap['class'] : $column->getClass()),
                'colSpan' => (isset($columnMap['colSpan']) ? $columnMap['colSpan'] : null)
            ]; 
        }
        return $this->fromArray($rowData, $className);
    }

    protected function getCellValue($entity, array $map)
    {
        if ($map['getter'] === null) {
            return '';
        }
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

    protected function getOrderDiscountSummary(Order $order)
    {
        if (!$order->getDiscountDescription()) {
            return '';
        }
        return "<b>Discount Summary</b><br />" . nl2br($order->getDiscountDescription());
    }

    protected function getOrderDiscountSubHeading()
    {
        return 'Order Discount:';
    }

    protected function getOrderDiscountTotal(Order $order)
    {
        return 0 - $order->getTotalDiscount();
    }

    protected function formatItemLink(Item $entity, $value)
    {
        if (empty($entity->getUrl())) {
            return $value;
        }
        return '<a href="' . $entity->getUrl() . '" target="_blank">' . $value . '</a>';
    }

    protected function formatCurrency($entity, $value)
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
