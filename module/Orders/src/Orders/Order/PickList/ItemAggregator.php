<?php
namespace Orders\Order\PickList;

use CG\Order\Shared\Collection as OrderCollection;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Item\Entity as Item;

class ItemAggregator
{
    protected $orders;
    protected $skuless;
    protected $itemsBySku;
    protected $itemsByTitle;
    protected $skus;

    public function __construct(OrderCollection $orders, $includeSkuless = false)
    {
        $this->orders = $orders;
        $this->includeSkuless = $includeSkuless;
    }

    public function __invoke()
    {
        $itemsBySku = [];
        $itemsByTitle = [];

        foreach($this->orders as $order) {
            /** @var Order $order */
            if($order->getItems()->count() === 0) {
                continue;
            }

            foreach($order->getItems() as $item) {
                /** @var Item $item */
                if($this->includeSkuless === true && ($item->getItemSku() === null || $item->getItemSku() === '')) {
                    $itemsByTitle[$item->getItemName()][] = $item;
                } elseif ($item->getItemSku() !== null && $item->getItemSku() !== '') {
                    $itemsBySku[$item->getItemSku()][] = $item;
                }
            }
        }

        $this->itemsBySku = $itemsBySku;
        $this->itemsByTitle = $itemsByTitle;
        $this->skus = array_keys($itemsBySku);
    }

    public function getItemsIndexedBySku()
    {
        return $this->itemsBySku;
    }

    public function getItemsIndexedByTitle()
    {
        return $this->itemsByTitle;
    }

    public function getSkus()
    {
        return $this->skus;
    }
}