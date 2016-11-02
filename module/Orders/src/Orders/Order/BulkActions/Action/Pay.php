<?php
namespace Orders\Order\BulkActions\Action;

use CG_UI\View\BulkActions\Action;
use Orders\Order\BulkActions\OrderAwareInterface;
use CG\Channel\Action\Order\Service as ActionDecider;
use CG\Channel\Action\Order\MapInterface as ActionDeciderMap;
use Zend\View\Model\ViewModel;
use SplObjectStorage;
use CG\Order\Shared\Entity as Order;

class Pay extends Action implements OrderAwareInterface
{
    const ICON = 'sprite-cash-22-black';

    protected $actionDecider;
    protected $urlView;

    public function __construct(
        ActionDecider $actionDecider,
        ViewModel $urlView,
        array $elementData = [],
        ViewModel $javascript = null,
        SplObjectStorage $subActions = null
    ) {
        parent::__construct(self::ICON, 'Mark as Paid', 'mark-paid', $elementData, $javascript, $subActions);
        $this
            ->setActionDecider($actionDecider)
            ->setUrlView($urlView)
            ->configure();
    }

    public function setActionDecider(ActionDecider $actionDecider)
    {
        $this->actionDecider = $actionDecider;
        return $this;
    }

    public function setUrlView(ViewModel $urlView)
    {
        $this->urlView = $urlView;
        return $this;
    }

    /**
     * @return ViewModel
     */
    public function getUrlView()
    {
        $this->urlView->setVariables(
            [
                'route' => 'Orders/pay',
                'parameters' => []
            ]
        );
        return $this->urlView;
    }

    protected function configure()
    {
        $this->addElementView($this->getUrlView());
        return $this;
    }

    public function setOrder(Order $order)
    {
        $actions = array_fill_keys($this->actionDecider->getAvailableActionsForOrder($order), true);
        $isEnabled = isset($actions[ActionDeciderMap::PAY]);
        $this->setEnabled($isEnabled);
    }
}
