<?php
namespace Products\Product\BulkActions\Action;

use CG\OrganisationUnit\Service as OuService;
use CG\User\ActiveUserInterface as ActiveUser;
use CG_UI\Module as CG_UI;
use CG_UI\View\BulkActions\Action;
use Products\Controller\ProductsJsonController;
use Products\Module;
use Zend\View\Model\ViewModel;

class ProductImport extends Action
{
    protected const ICON = 'sprite-csv-upload-22-black';
    protected const ROUTE = [Module::ROUTE, ProductsJsonController::ROUTE_PRODUCT_CSV_IMPORT];

    /** @var ActiveUser */
    protected $activeUser;
    /** @var OuService */
    protected $ouService;

    public function __construct(
        ActiveUser $activeUser,
        OuService $ouService,
        ViewModel $urlView,
        array $elementData = [],
        ViewModel $javascript = null,
        \SplObjectStorage $subActions = null
    ) {
        parent::__construct(static::ICON, 'Upload products', 'productImport', $elementData, $javascript, $subActions);
        $this->activeUser = $activeUser;
        $this->ouService = $ouService;
        $this->configure($urlView);
    }

    protected function configure(ViewModel $urlView)
    {
        $this->addElementView($urlView->setVariables(['route' => implode('/', static::ROUTE), 'parameters' => []]));
        $this->getJavascript()->setVariables(
            [
                'templateMap' => [
                    'popup' => Module::PUBLIC_FOLDER . 'template/popups/productImport.html',
                    'fileUpload' => CG_UI::PUBLIC_FOLDER . 'templates/elements/file-upload.mustache',
                ],
            ]
        );
        return $this;
    }
}
