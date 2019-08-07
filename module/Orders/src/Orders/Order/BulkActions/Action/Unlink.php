<?php
namespace Orders\Order\BulkActions\Action;

use CG\Order\Shared\Entity as Order;
use CG_UI\View\BulkActions\Action;
use Orders\Order\BulkActions\OrderAwareInterface;
use SplObjectStorage;
use Zend\View\Model\ViewModel;

class Unlink extends Action implements OrderAwareInterface
{
    const ICON = 'sprite-broken-link-22-black';

    protected $urlView;

    public function __construct(
        ViewModel $urlView,
        array $elementData = [],
        ViewModel $javascript = null,
        SplObjectStorage $subActions = null
    ) {
        parent::__construct(self::ICON, 'Unlink', 'unlink', $elementData, $javascript, $subActions);
        $this->urlView = $urlView;
        $this->configure();
    }

    protected function configure()
    {
        $this->urlView->setVariables(
            [
                'route' => 'Orders/unlink',
                'parameters' => []
            ]
        );
        $this->addElementView($this->urlView);
        return $this;
    }

    public function setOrder(Order $order)
    {
        $isEnabled = ($order->isLinkable() && $order->getOrderLinks() != null && $order->getOrderLinks()->count() > 0);
        $this->setEnabled($isEnabled);
    }
}
