<?php
namespace CG\ShipStation\Messages;

use CG\Order\Shared\Courier\Label\OrderData;
use CG\Order\Shared\Courier\Label\OrderParcelsData\Collection as OrderParcelsData;
use CG\Order\Shared\ShippableInterface as Order;
use CG\OrganisationUnit\Entity as OrganisationUnit;
use CG\ShipStation\Messages\Customs\Item;

class Customs
{
    const CONTENTS_GIFT = 'gift';
    const CONTENTS_MERCH = 'merchandise';
    const CONTENTS_RETURN = 'returned_goods';
    const CONTENTS_DOCS = 'documents';
    const CONTENTS_SAMPLE = 'sample';

    const NON_DELIVERY_ABANDONED = 'treat_as_abandoned';
    const NON_DELIVERY_RETURN = 'return_to_sender';

    /** @var string */
    protected $contents;
    /** @var string */
    protected $nonDelivery;
    /** @var Item[] */
    protected $items;

    public function __construct(
        string $contents,
        string $nonDelivery,
        Item ...$items
    ) {
        $this->contents = $contents;
        $this->nonDelivery = $nonDelivery;
        $this->items = $items;
    }

    public static function createFromOrder(
        Order $order,
        OrganisationUnit $rootOu
    ): self {
        $items = [];
        foreach ($order->getItems() as $orderItem) {
            $items[] = Item::createFromOrderItem($orderItem, $rootOu);
        }
        return new self(
            static::CONTENTS_MERCH,
            static::NON_DELIVERY_RETURN,
            ...$items
        );
    }

    public function toArray(): array
    {
        $array = [
            'contents' => $this->getContents(),
            'non_delivery' => $this->getNonDelivery(),
            'customs_items' => []
        ];
        foreach ($this->getItems() as $item) {
            $array['customs_items'][] = $item->toArray();
        }
        return $array;
    }

    public function getContents(): string
    {
        return $this->contents;
    }

    public function getNonDelivery(): string
    {
        return $this->nonDelivery;
    }

    /**
     * @return Item[]
     */
    public function getItems(): array
    {
        return $this->items;
    }
}