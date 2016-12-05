<?php
namespace Orders\Order\BulkActions\Action;

use CG_UI\View\BulkActions\Action;
use Orders\Module;
use Orders\Controller\CourierController;
use CG\Channel\Action\Order\Service as ActionDecider;
use CG\Channel\Action\Order\MapInterface as ActionDeciderMap;
use Orders\Order\BulkActions\OrderAwareInterface;
use CG\Order\Shared\Entity as Order;
use Zend\View\Model\ViewModel;
use SplObjectStorage;

class Courier extends Action implements OrderAwareInterface
{
    protected $urlView;
    protected $actionDecider;

    public function __construct(
        ActionDecider $actionDecider,
        ViewModel $urlView,
        array $elementData = [],
        ViewModel $javascript = null,
        SplObjectStorage $subActions = null
    ) {
        parent::__construct('sprite-courier-22-black', 'Courier', 'courier', $elementData, $javascript, $subActions);
        $this->setUrlView($urlView)->configure();
        $this->setActionDecider($actionDecider);
    }

    public function setUrlView(ViewModel $urlView)
    {
        $this->urlView = $urlView;
        $this->urlView->setVariables(
            [
                'route' => Module::ROUTE.'/'.CourierController::ROUTE.'/'.CourierController::ROUTE_REVIEW,
                'parameters' => []
            ]
        );
        return $this;
    }

    /**
     * @return ViewModel
     */
    public function getUrlView()
    {
        return $this->urlView;
    }

    public function setActionDecider($actionDecider)
    {
        $this->actionDecider = $actionDecider;
        return $this;
    }

    protected function configure()
    {
        $this->addElementView($this->getUrlView());
        return $this;
    }

    public function setOrder(Order $order)
    {
        $actions = array_fill_keys($this->actionDecider->getAvailableActionsForOrder($order), true);
        $this->setEnabled(isset($actions[ActionDeciderMap::DISPATCH]));
    }
} 