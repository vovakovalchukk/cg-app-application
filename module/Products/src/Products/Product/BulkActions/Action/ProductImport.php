<?php
namespace Products\Product\BulkActions\Action;

use CG\FeatureFlags\Service as FeatureFlagService;
use CG\OrganisationUnit\Service as OuService;
use CG\User\ActiveUserInterface as ActiveUser;
use CG_UI\Module as CG_UI;
use CG_UI\View\BulkActions\Action;
use CG_UI\View\BulkActions\FeatureFlagRequiredInterface;
use Products\Controller\ProductsJsonController;
use Products\Module;
use Zend\View\Model\ViewModel;

class ProductImport extends Action implements FeatureFlagRequiredInterface
{
    protected const ICON = 'sprite-csv-upload-22-black';
    protected const ROUTE = [Module::ROUTE, ProductsJsonController::ROUTE_PRODUCT_CSV_IMPORT];
    protected const FEATURE_FLAG = 'SimpleProductCsvImport';

    /** @var FeatureFlagService */
    protected $featureFlagService;
    /** @var ActiveUser */
    protected $activeUser;
    /** @var OuService */
    protected $ouService;

    public function __construct(
        FeatureFlagService $featureFlagService,
        ActiveUser $activeUser,
        OuService $ouService,
        ViewModel $urlView,
        array $elementData = [],
        ViewModel $javascript = null,
        \SplObjectStorage $subActions = null
    ) {
        parent::__construct(static::ICON, 'Upload products', 'productImport', $elementData, $javascript, $subActions);
        $this->featureFlagService = $featureFlagService;
        $this->activeUser = $activeUser;
        $this->ouService = $ouService;
        $this->configure($urlView);
    }

    public function isFeatureEnabled(): bool
    {
        return $this->featureFlagService->isActive(
            static::FEATURE_FLAG,
            $this->ouService->getRootOuFromOuId(
                $this->activeUser->getActiveUserRootOrganisationUnitId()
            )
        );
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
