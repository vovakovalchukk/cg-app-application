<?php
namespace Products\Product\BulkActions\Action;

use CG\FeatureFlags\Service as FeatureFlagsService;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Stock\Import\UpdateOptions;
use CG\User\ActiveUserInterface;
use CG_UI\Module as CG_UI;
use CG_UI\View\BulkActions\Action;
use CG_UI\View\BulkActions\FeatureFlagRequiredInterface;
use Products\Module;
use SplObjectStorage;
use Zend\View\Model\ViewModel;


class ProductLinkImport extends Action implements FeatureFlagRequiredInterface
{
    const ICON = 'sprite-csv-upload-22-black';
    const FEATURE_FLAG = 'Product Link Export';

    protected $urlView;
    /** @var FeatureFlagsService */
    protected $featureFlagService;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var OrganisationUnitService */
    protected $ouService;

    public function __construct(
        ViewModel $urlView,
        FeatureFlagsService $featureFlagService,
        ActiveUserInterface $activeUserContainer,
        OrganisationUnitService $ouService,
        ViewModel $javascript = null,
        SplObjectStorage $subActions = null,
        array $elementData = []
    ) {
        parent::__construct(static::ICON, "Product Link Import", "productLinkImport", $elementData, $javascript, $subActions);
        $this->setUrlView($urlView)->configure();
        $this->featureFlagService = $featureFlagService;
        $this->activeUserContainer = $activeUserContainer;
        $this->ouService = $ouService;
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

    public function isFeatureEnabled(): bool
    {
        return $this->featureFlagService->isActive(
            static::FEATURE_FLAG,
            $this->ouService->getRootOuFromOuId(
                $this->activeUserContainer->getActiveUserRootOrganisationUnitId()
            )
        );
    }
}
