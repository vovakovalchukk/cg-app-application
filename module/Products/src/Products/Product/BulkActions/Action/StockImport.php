<?php
namespace Products\Product\BulkActions\Action;

use CG_UI\View\BulkActions\Action;
use Zend\View\Model\ViewModel;
use SplObjectStorage;
use Products\Module;
use CG_UI\Module as CG_UI;

class StockImport extends Action
{
    const ICON = 'sprite-cancel-22-black'; // TODO Change this to be the correct icon

    protected $urlView;

    public function __construct(
        ViewModel $urlView,
        array $elementData = [],
        ViewModel $javascript = null,
        SplObjectStorage $subActions = null
    ) {
        parent::__construct(static::ICON, "Stock Import", "stockImport", $elementData, $javascript, $subActions);
        $this->setUrlView($urlView)->configure();
    }

    /**
     * @return ViewModel
     */
    public function getUrlView()
    {
        $this->urlView->setVariables(
            [
                'route' => 'Products/stockCsvImport',
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
                'type' => "stockImport",
                'templateMap' => [
                    'popup' => Module::PUBLIC_FOLDER . 'template/popups/updateOptions.html',
                    'select' => CG_UI::PUBLIC_FOLDER . 'templates/elements/custom-select.mustache',
                ],
            ]
        );
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
