<?php
namespace Orders\Order\BulkActions\Action;

use CG_UI\View\BulkActions\Action;
use Zend\View\Model\ViewModel;
use SplObjectStorage;
use CG\Order\Shared\Cancel\Reasons;
use CG\Channel\Action\Order\Service as ActionDecider;
use CG\Channel\Action\Order\MapInterface as ActionDeciderMap;
use Orders\Order\BulkActions\OrderAwareInterface;
use CG\Order\Shared\Entity as Order;
use CG\Order\Shared\Cancel\Value as CancelValue;
use Orders\Module;
use CG_UI\Module as CG_UI;

class Cancel extends Action implements OrderAwareInterface
{
    const ICON = 'sprite-cancel-18-black';
    const TYPE = CancelValue::CANCEL_TYPE;
    const ALLOWED_ACTION = ActionDeciderMap::CANCEL;

    protected $actionDecider;
    protected $urlView;

    public function __construct(
        ActionDecider $actionDecider,
        ViewModel $urlView,
        array $elementData = [],
        ViewModel $javascript = null,
        SplObjectStorage $subActions = null
    ) {
        parent::__construct(static::ICON, ucwords(static::TYPE), static::TYPE, $elementData, $javascript, $subActions);
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
                'route' => 'Orders/cancel',
                'parameters' => []
            ]
        );
        return $this->urlView;
    }

    protected function configure()
    {
        $this->addElementView($this->getUrlView());
        $this->getJavascript()->setVariables(
            [
                'cancellationReasons' => json_encode(Reasons::getAllCancellationReasons()),
                'type' => static::TYPE,
                'templateMap' => [
                    'popup' => Module::PUBLIC_FOLDER . 'template/popups/cancelOptions.html',
                    'select' => CG_UI::PUBLIC_FOLDER . 'templates/elements/custom-select.mustache',
                ],
            ]
        );
        return $this;
    }

    public function setOrder(Order $order)
    {
        $actions = array_fill_keys($this->getActionDecider()->getAvailableActionsForOrder($order), true);
        $this->setEnabled(isset($actions[static::ALLOWED_ACTION]));
    }
}
