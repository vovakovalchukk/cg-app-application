<?php
namespace Orders\Order\BulkActions\Action;

use CG_UI\View\BulkActions\Action;
use Zend\View\Model\ViewModel;
use SplObjectStorage;

class ToCsv extends Action
{
    protected $urlView;

    public function __construct(
        ViewModel $urlView,
        array $elementData = [],
        ViewModel $javascript = null,
        SplObjectStorage $subActions = null
    ) {
        parent::__construct('sprite-csv-download-22-black', 'To CSV', 'toCsv', $elementData, $javascript, $subActions);
        $this->setUrlView($urlView)->configure();
    }

    public function setUrlView(ViewModel $urlView)
    {
        $this->urlView = $urlView;
        $this->urlView->setVariables(
            [
                'route' => 'Orders/to_csv',
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