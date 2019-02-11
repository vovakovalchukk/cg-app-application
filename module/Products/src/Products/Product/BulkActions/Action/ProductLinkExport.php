<?php
namespace Products\Product\BulkActions\Action;

use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\User\ActiveUserInterface;
use CG_UI\View\BulkActions\Action;
use CG_UI\View\BulkActions\FeatureFlagRequiredInterface;
use Zend\View\Model\ViewModel;
use SplObjectStorage;
use CG\FeatureFlags\Service as FeatureFlagsService;

class ProductLinkExport extends Action implements FeatureFlagRequiredInterface
{
    const ICON = 'sprite-csv-download-22-black';
    const FEATURE_FLAG = 'Product Link Export';

    /** @var FeatureFlagsService */
    protected $featureFlagService;
    /** @var ActiveUserInterface */
    protected $activeUserContainer;
    /** @var OrganisationUnitService */
    protected $ouService;

    public function __construct(
        FeatureFlagsService $featureFlagService,
        ActiveUserInterface $activeUserContainer,
        OrganisationUnitService $ouService,
        ViewModel $urlView,
        array $elementData = [],
        ViewModel $javascript = null,
        SplObjectStorage $subActions = null
    ) {
        parent::__construct(static::ICON, "Product Link Export", "productLinkExport", $elementData, $javascript, $subActions);
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
                'route' => 'Products/productLinkCsvExport',
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
