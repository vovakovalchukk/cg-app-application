<?php
namespace Orders\Order\BulkActions\Action;

use CG\Channel\Action\Order\MapInterface as ActionDeciderMap;
use CG\Channel\Action\Order\Service as ActionDecider;
use CG\Order\Shared\Cancel\Reasons;
use CG\Order\Shared\Cancel\Value as CancelValue;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Item\Collection as ItemCollection;
use CG\Order\Shared\Item\Entity as Item;
use CG\Order\Shared\Item\Refund\Collection as ItemRefundCollection;
use CG\Order\Shared\Item\Refund\Entity as ItemRefund;
use CG\Order\Shared\Item\Refund\Filter as ItemRefundFilter;
use CG\Order\Shared\Item\Refund\Service as ItemRefundService;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG_UI\View\BulkActions\Action;
use Orders\Order\BulkActions\OrderAwareInterface;
use SplObjectStorage;
use Zend\View\Model\ViewModel;

class PartialRefund extends Action implements OrderAwareInterface
{
    const ICON = 'sprite-accounting-22-black';
    const ALLOWED_ACTION = ActionDeciderMap::PARTIAL_REFUND;

    /** @var ActionDecider */
    protected $actionDecider;
    /** @var ViewModel */
    protected $urlView;
    /** @var ItemRefundService */
    protected $itemRefundService;

    public function __construct(
        ActionDecider $actionDecider,
        ViewModel $urlView,
        ItemRefundService $itemRefundService,
        array $elementData = [],
        ViewModel $javascript = null,
        SplObjectStorage $subActions = null
    ) {
        parent::__construct(static::ICON, 'Partial Refund', 'partialRefund', $elementData, $javascript, $subActions);
        $this->actionDecider = $actionDecider;
        $this->urlView = $urlView;
        $this->itemRefundService = $itemRefundService;
    }

    public function setOrder(Order $order)
    {
        $actions = array_fill_keys($this->actionDecider->getAvailableActionsForOrder($order), true);
        $this->setEnabled(isset($actions[static::ALLOWED_ACTION]));
        $this->configure($order);
    }

    protected function configure(Order $order)
    {
        $this->addElementView($this->getUrlView());
        $this->getJavascript()->setVariables(
            [
                'orderId' => $order->getId(),
                'refundReasons' => json_encode(Reasons::getAllRefundReasons()),
                'items' => $this->getRefundableItemsData($order),
            ]
        );
        return $this;
    }

    public function getUrlView()
    {
        $this->urlView->setVariables(
            [
                'route' => 'Orders/cancel',
                'parameters' => []
            ]
        );
        return $this->urlView;
    }

    protected function getRefundableItemsData(Order $order): array
    {
        $data = [];
        $items = $order->getItems();
        $itemRefunds = $this->fetchItemRefundsForItems($items);
        /** @var Item $item */
        foreach ($items as $item) {
            $data[] = [
                'id' => $item->getId(),
                'sku' => $item->getItemSku(),
                'name' => $item->getItemName(),
                'amount' => $item->getRefundableAmount($itemRefunds->getBy('itemId', $item->getId())),
            ];
        }
        return $data;
    }

    protected function fetchItemRefundsForItems(ItemCollection $items): ItemRefundCollection
    {
        try {
            return $this->itemRefundService->fetchCollectionByItemIds($items->getIds());
        } catch (NotFound $e) {
            return new ItemRefundCollection(ItemRefund::class, 'empty');
        }
    }

    protected function calculateRefundableAmountForItem(Item $item, ItemRefundCollection $itemRefunds): float
    {
        $amount = $item->getLineTotal();
        /** @var ItemRefund $itemRefund */
        foreach ($itemRefunds as $itemRefund) {
            $amount -= $itemRefund->getAmount();
        }
        $amount = round($amount, 2);
        return ($amount >= 0 ? $amount : 0);
    }
}
