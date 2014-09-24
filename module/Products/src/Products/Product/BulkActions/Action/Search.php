<?php
namespace Products\Product\BulkActions\Action;

use CG_UI\View\BulkActions\Action;
use Zend\View\Model\ViewModel;
use SplObjectStorage;
use CG\Channel\Action\Order\Service as ActionDecider;
use CG\Channel\Action\Order\MapInterface as ActionDeciderMap;

class Search extends Action
{
    const ICON = '';
    const TYPE = 'search';
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
                'route' => 'Orders/batch/create',
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
}
