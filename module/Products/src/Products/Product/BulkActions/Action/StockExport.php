<?php
namespace Products\Product\BulkActions\Action;

use CG_UI\View\BulkActions\Action;
use Zend\View\Model\ViewModel;
use SplObjectStorage;

class StockExport extends Action
{
    const ICON = 'sprite-csv-download-22-black';

    public function __construct(
        ViewModel $urlView,
        array $elementData = [],
        ViewModel $javascript = null,
        SplObjectStorage $subActions = null
    ) {
        parent::__construct(static::ICON, "Stock Export", "stockExport", $elementData, $javascript, $subActions);
        $this->setUrlView($urlView)->configure();
    }

    /**
     * @return ViewModel
     */
    public function getUrlView()
    {
        $this->urlView->setVariables(
            [
                'route' => 'Products/stockCsvExport',
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

    /**
     * @return self
     */
    public function setUrlView(ViewModel $urlView)
    {
        $this->urlView = $urlView;
        return $this;
    }
}
