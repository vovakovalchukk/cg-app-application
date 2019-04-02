<?php

namespace Orders\Order\Table\Row;

use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Item\Entity as Item;
use CG\Order\Shared\Item\GiftWrap\Entity as GiftWrap;
use CG\Product\Link\Entity as ProductLink;
use CG_UI\View\Table\Column\Collection as Columns;
use CG_UI\View\Table\Row\Mapper as UIMapper;
use Zend\I18n\View\Helper\CurrencyFormat;
use CG_Mustache\View\Renderer as MustacheRenderer;
use CG_UI\View\Prototyper\ViewModelFactory;

class Mapper extends UIMapper
{
    const COLUMN_SKU = 'SKU';
    const COLUMN_PRODUCT = 'Product Name';
    const COLUMN_VARIATIONS = 'Variation Attributes';
    const COLUMN_QUANTITY = 'Quantity';
    const COLUMN_CUSTOMISATION = 'Customisation';
    const COLUMN_PRICE = 'Price inc. VAT';
    const COLUMN_DISCOUNT = 'Discount Total';
    const COLUMN_TOTAL = 'Line Total';
    const GIFT_SKU_HEADER = 'GIFT';

    /** @var MustacheRenderer */
    protected $mustacheRenderer;
    /** @var ViewModelFactory */
    protected $viewModelFactory;

    protected $currencyFormat;
    protected $order;

    protected $mapItem = [
        self::COLUMN_SKU => ['getter' => 'getItemSku', 'callback' => null],
        self::COLUMN_PRODUCT => ['getter' => 'getItemName', 'callback' => 'formatItemName'],
        self::COLUMN_VARIATIONS => ['getter' => 'getItemVariationAttribute', 'callback' => 'formatVariationAttributes'],
        self::COLUMN_QUANTITY => ['getter' => 'getItemQuantity', 'callback' => null],
        self::COLUMN_PRICE => ['getter' => 'getItemPrice', 'callback' => 'formatCurrency'],
        self::COLUMN_DISCOUNT => ['getter' => 'getItemDiscountTotal', 'callback' => 'formatCurrency'],
        self::COLUMN_TOTAL => ['getter' => 'getItemLineTotal', 'callback' => 'formatCurrency'],
        self::COLUMN_CUSTOMISATION => ['getter' => 'getCustomisation', 'callback' => 'formatCustomisation'],
    ];
    protected $mapDiscount = [
        self::COLUMN_SKU => ['getter' => 'getOrderDiscountSummary', 'callback' => null, 'colSpan' => 3],
        self::COLUMN_PRICE => ['getter' => 'getOrderDiscountSubHeading', 'callback' => null, 'colSpan' => 2, 'class' => ''],
        self::COLUMN_TOTAL => ['getter' => 'getOrderDiscountTotal', 'callback' => 'formatCurrency'],
    ];
    protected $mapGiftWrap = [
        self::COLUMN_SKU => ['getter' => 'getGiftWrapSkuColumnMessage', 'callback' => null],
        self::COLUMN_PRODUCT => ['getter' => 'getGiftProductNameColumn', 'callback' => null],
        self::COLUMN_QUANTITY => ['getter' => null, 'callback' => null],
        self::COLUMN_PRICE => ['getter' => 'getGiftWrapPrice', 'callback' => 'formatCurrency'],
        self::COLUMN_DISCOUNT => ['getter' => null, 'callback' => null],
        self::COLUMN_TOTAL => ['getter' => 'getGiftWrapPrice', 'callback' => 'formatCurrency'],
    ];

    protected $optionalColumns = [
        self::COLUMN_VARIATIONS => ['getter' => 'getItemVariationAttribute'],
        self::COLUMN_CUSTOMISATION => ['getter' => 'getCustomisation'],
    ];

    public function __construct(
        CurrencyFormat $currencyFormat,
        MustacheRenderer $mustacheRenderer,
        ViewModelFactory $viewModelFactory
    ) {
        $this->setCurrencyFormat($currencyFormat);
        $this->mustacheRenderer = $mustacheRenderer;
        $this->viewModelFactory = $viewModelFactory;
    }

    public function fromItem(Item $item, Order $order, Columns $columns, $className = null, array $productLinks = [])
    {
        $this->setOrder($order);
        $map = $this->mapItem;
        return $this->fromEntity($item, $map, $columns, $className, $productLinks);
    }

    public function fromOrderDiscount(Order $order, Columns $columns, $className = null)
    {
        $this->setOrder($order);
        $map = $this->mapDiscount;
        return $this->fromEntity($order, $map, $columns, $className);
    }

    public function fromGiftWrap(GiftWrap $giftWrap, Order $order, Columns $columns, $className = null)
    {
        $this->setOrder($order);
        $map = $this->mapGiftWrap;
        return $this->fromEntity($giftWrap, $map, $columns, $className);
    }

    public function fromProductLink(
        $sku,
        $quantity,
        $className = null,
        $isFirstLinkedProduct = null
    ) {
        $rowData = [];
        $rowData[] = [
            'content' => $sku,
            'class' => 'product-link-td'
        ];

        $loadingSpinnerHTML = '';
        if ($isFirstLinkedProduct) {
            $loadingSpinnerView = $this->viewModelFactory->newInstance();
            $loadingSpinnerView->setTemplate('elements/loadingIndicator.mustache');

            $loadingSpinner = $this->mustacheRenderer->render(
                $loadingSpinnerView
            );
            $loadingSpinnerHTML = '<div class="u-margin-center" style="max-height:12px; max-width: 12px;">' . $loadingSpinner . '</div>';
        }
        $rowData[] = [
            'content' => $loadingSpinnerHTML,
            'class' => 'product-link-td js-linked-product-name',
            'attributes' => [
                'data-sku' => $sku
            ]
        ];
        $rowData[] = [
            'content' => $quantity,
            'class' => 'product-link-td'
        ];
        $rowData[] = [
            'content' => '',
            'class' => 'product-link-td'
        ];
        $rowData[] = [
            'content' => '',
            'class' => 'product-link-td'
        ];
        $rowData[] = [
            'content' => '',
            'class' => 'product-link-td'
        ];

        return $this->fromArray($rowData, $className);
    }

    protected function fromEntity($entity, $map, Columns $columns, $className = null, array $productLinks = [])
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
                'content' => $this->formatCellValue($value, $entity, $columnMap, $productLinks),
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

    protected function formatCellValue($value, $entity, array $map, array $productLinks = [])
    {
        if (!$map['callback'] || !is_callable([$this, $map['callback']])) {
            return $value;
        }
        return $this->{$map['callback']}($entity, $value, $productLinks);
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

    protected function getGiftWrapSkuColumnMessage()
    {
        return static::GIFT_SKU_HEADER;
    }

    protected function getGiftProductNameColumn(Giftwrap $giftWrap)
    {
        $wrapType = $this->getGiftWrapType($giftWrap) . ((!empty($this->getGiftWrapType($giftWrap))) ? '<br>' : '');
        $wrapMessage = $this->getGiftWrapMessage($giftWrap);
        return $wrapType . $wrapMessage;
    }

    protected function getGiftWrapType(GiftWrap $giftWrap)
    {
        if(!$giftWrap->getGiftWrapType()) {
            return '';
        }
        return '<div class="wrap-type-holder"><b>Wrap: </b>' . strtoupper($giftWrap->getGiftWrapType()) . '</div>';
    }

    protected function getGiftWrapMessage(GiftWrap $giftWrap)
    {
        if (!$giftWrap->getGiftWrapMessage()) {
            return '';
        }
        return '<div class="wrap-message-holder"><b>Message: </b> ' . nl2br($giftWrap->getGiftWrapMessage()) . '</div>';
    }

    protected function formatItemName(Item $entity, $value, array $productLinks)
    {
        $value = htmlentities($value, ENT_QUOTES | ENT_HTML401);

        $linkedProductIcon = '';
        if (isset($productLinks[$entity->getItemSku()])) {
            $linkedProductIcon = '<span class="sprite sprite-linked-22-blue"></span>';
        }

        $values = explode(PHP_EOL, $value);
        for ($i = 1; $i < count($values); $i++) {
            if (preg_match('/^(?<name>[^:]*:)(?<value>.*)$/', $values[$i], $info)) {
                $values[$i] = sprintf('<i><b>%s</b>%s</i>', $info['name'], $info['value']);
            } else {
                $values[$i] = sprintf('<i>%s</i>', $values[$i]);
            }
        }

        if (empty($entity->getUrl())) {
            return '<div class="product-table-item">' . nl2br(implode(PHP_EOL, $values)) . $linkedProductIcon . '</div>';
        }

        return '<a class="product-table-item-link" href="' . $entity->getUrl() . '" target="_blank">' . array_shift($values) . $linkedProductIcon . '</a>'
            . '<div class="product-table-item">' . nl2br(implode(PHP_EOL, $values)) . '</div>';
    }

    protected function formatCurrency($entity, $value)
    {
        $currencyCode = $this->order->getCurrencyCode();
        $formatter = $this->currencyFormat;
        return $formatter($value, $currencyCode);
    }

    protected function formatVariationAttributes(Item $entity, array $attributes): string
    {
        $mergedKeyVals = [];
        foreach($attributes as $attribute => $value) {
            $mergedKeyVals[] = $attribute . ': ' . $value;
        }
        return implode("<br />", $mergedKeyVals);
    }

    protected function formatCustomisation(Item $entity, $value)
    {
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return '<a href="'.$value.'">Download</a>';
        } else {
            return '<div>'.substr($value, 0, 40).'...</div>';
        }
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

    public function getOptionalColumns(): array
    {
        return $this->optionalColumns;
    }
}
