<?php
namespace Orders\Order\BulkActions\Action;

use CG_UI\View\BulkActions\Action;
use Zend\View\Model\ViewModel;
use SplObjectStorage;
use CG\Channel\Action\Order\Service as ActionDecider;
use CG\Channel\Action\Order\MapInterface as ActionDeciderMap;
use Orders\Order\BulkActions\OrderAwareInterface;
use CG\Order\Shared\Entity as Order;

class Dispatch extends Action implements OrderAwareInterface
{
    protected $actionDecider;
    protected $urlView;

    public function __construct(
        ActionDecider $actionDecider,
        ViewModel $urlView,
        array $elementData = [],
        ViewModel $javascript = null,
        SplObjectStorage $subActions = null
    ) {
        parent::__construct('sprite-dispatch-22-black', 'Dispatch', 'dispatch', $elementData, $javascript, $subActions);
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

    /**
     * @return ActionDecider
     */
    public function getActionDecider()
    {
        return $this->actionDecider;
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
                'route' => 'Orders/dispatch',
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
        $actions = array_fill_keys($this->getActionDecider()->getAvailableActionsForOrder($order), true);
        $this->setEnabled(isset($actions[ActionDeciderMap::DISPATCH]));
    }
}