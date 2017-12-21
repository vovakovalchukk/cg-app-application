<?php
namespace Products\Product\BulkActions\Action;

use CG_UI\View\BulkActions\Action;
use Zend\View\Model\ViewModel;
use Products\Module;
use CG_UI\Module as CG_UI;

class ListingsImport extends Action
{
    const ICON = 'sprite-csv-upload-22-black';

    /** @var  ViewModel */
    protected $urlView;

    public function __construct(
        ViewModel $urlView,
        array $elementData = [],
        ViewModel $javascript = null
    ) {
        parent::__construct(static::ICON, "Listings Import", "listingsImport", $elementData, $javascript);
        $this->setUrlView($urlView)->configure();
    }


    /**
     * @return ViewModel
     */
    public function getUrlView()
    {
        $this->urlView->setVariables(
            [
                'route' => 'Products/import',
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
                'updateOptions' => json_encode($this->getUpdateOptions()),
                'type' => "listingsImport",
                'templateMap' => [
                    'popup' => Module::PUBLIC_FOLDER . 'template/popups/updateOptions.html',
                    'select' => CG_UI::PUBLIC_FOLDER . 'templates/elements/custom-select.mustache',
                ],
            ]
        );
        return $this;
    }

    protected function getUpdateOptions()
    {
        return [
            ['title' => 'ebay']
        ];
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
