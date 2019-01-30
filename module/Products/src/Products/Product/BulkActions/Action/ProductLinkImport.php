<?php
namespace Products\Product\BulkActions\Action;

use CG_UI\View\BulkActions\Action;
use Zend\View\Model\ViewModel;
use SplObjectStorage;
use Products\Module;
use CG_UI\Module as CG_UI;
use CG\Stock\Import\UpdateOptions;

class ProductLinkImport extends Action
{
    const ICON = 'sprite-csv-upload-22-black';

    protected $urlView;

    public function __construct(
        ViewModel $urlView,
        array $elementData = [],
        ViewModel $javascript = null,
        SplObjectStorage $subActions = null
    ) {
        parent::__construct(static::ICON, "Product Link Import", "productLinkImport", $elementData, $javascript, $subActions);
        $this->setUrlView($urlView)->configure();
    }

    /**
     * @return ViewModel
     */
    public function getUrlView()
    {
        $this->urlView->setVariables(
            [
                'route' => 'Products/productLinkCsvImport',
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
                'type' => "stockImport",
                'templateMap' => [
                    'popup' => Module::PUBLIC_FOLDER . 'template/popups/updateOptions.html',
                    'select' => CG_UI::PUBLIC_FOLDER . 'templates/elements/custom-select.mustache',
                    'fileUpload' => CG_UI::PUBLIC_FOLDER . 'templates/elements/file-upload.mustache',
                ],
            ]
        );
        return $this;
    }

    protected function getUpdateOptions()
    {
        return [
            ["title" => UpdateOptions::SET_STOCK],
            ["title" => UpdateOptions::ADD_TO_STOCK],
            ["title" => UpdateOptions::REMOVE_FROM_STOCK]
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
