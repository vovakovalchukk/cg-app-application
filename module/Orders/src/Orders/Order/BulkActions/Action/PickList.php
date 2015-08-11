<?php
namespace Orders\Order\BulkActions\Action;

use CG_UI\View\BulkActions\Action;
use Zend\View\Model\ViewModel;
use SplObjectStorage;

class PickList extends Action
{
    protected $urlView;

    public function __construct(
        ViewModel $urlView,
        array $elementData = [],
        ViewModel $javascript = null,
        SplObjectStorage $subActions = null
    ) {
        parent::__construct('sprite-picklist-22-black', 'Pick List', 'pickList', $elementData, $javascript, $subActions);
        $this->setUrlView($urlView)->configure();
    }

    public function setUrlView(ViewModel $urlView)
    {
        $this->urlView = $urlView;
        $this->urlView->setVariables(
            [
                'route' => 'Orders/pick_list',
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

    protected function configure()
    {
        $this->addElementView($this->getUrlView());
        return $this;
    }
}
